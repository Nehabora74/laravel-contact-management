<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->authorizeResource(Company::class, 'company');
    }

    /**
     * Display a listing of companies
     */
    public function index(Request $request)
    {
        $companies = Company::query()
            ->forUser(auth()->id())
            ->withCount('contacts')
            ->when($request->search, fn($q, $search) => $q->search($search))
            ->when($request->industry, fn($q, $industry) => $q->byIndustry($industry))
            ->orderBy($request->sort ?? 'name', $request->direction ?? 'asc')
            ->paginate(25)
            ->withQueryString();

        // Get unique industries for filter
        $industries = Company::forUser(auth()->id())
            ->whereNotNull('industry')
            ->distinct()
            ->pluck('industry');

        return view('companies.index', compact('companies', 'industries'));
    }

    /**
     * Show the form for creating a new company
     */
    public function create()
    {
        return view('companies.create');
    }

    /**
     * Store a newly created company
     */
    public function store(StoreCompanyRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('companies', 'public');
        }

        $company = Company::create($data);

        return redirect()
            ->route('companies.show', $company)
            ->with('success', 'Company created successfully!');
    }

    /**
     * Display the specified company
     */
    public function show(Company $company)
    {
        $company->load(['contacts' => fn($q) => $q->orderBy('first_name')]);
        $company->loadCount('contacts');

        return view('companies.show', compact('company'));
    }

    /**
     * Show the form for editing the company
     */
    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    /**
     * Update the specified company
     */
    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }
            $data['logo'] = $request->file('logo')->store('companies', 'public');
        }

        $company->update($data);

        return redirect()
            ->route('companies.show', $company)
            ->with('success', 'Company updated successfully!');
    }

    /**
     * Remove the specified company
     */
    public function destroy(Company $company)
    {
        if ($company->logo) {
            Storage::disk('public')->delete($company->logo);
        }

        $company->delete();

        return redirect()
            ->route('companies.index')
            ->with('success', 'Company deleted successfully!');
    }
}
