<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Company;
use App\Models\Group;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Exports\ContactsExport;
use App\Imports\ContactsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->authorizeResource(Contact::class, 'contact');
    }

    /**
     * Display a listing of contacts
     */
    public function index(Request $request)
    {
        $contacts = Contact::query()
            ->forUser(auth()->id())
            ->with(['company', 'groups'])
            ->when($request->search, fn($q, $search) => $q->search($search))
            ->when($request->status, fn($q, $status) => $q->byStatus($status))
            ->when($request->company, fn($q, $company) => $q->byCompany($company))
            ->when($request->group, fn($q, $group) => $q->byGroup($group))
            ->orderBy($request->sort ?? 'first_name', $request->direction ?? 'asc')
            ->paginate(25)
            ->withQueryString();

        $companies = Company::forUser(auth()->id())->orderBy('name')->get();
        $groups = Group::forUser(auth()->id())->withCount('contacts')->get();

        return view('contacts.index', compact('contacts', 'companies', 'groups'));
    }

    /**
     * Show the form for creating a new contact
     */
    public function create()
    {
        $companies = Company::forUser(auth()->id())->orderBy('name')->get();
        $groups = Group::forUser(auth()->id())->get();

        return view('contacts.create', compact('companies', 'groups'));
    }

    /**
     * Store a newly created contact
     */
    public function store(StoreContactRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('contacts', 'public');
        }

        $contact = Contact::create($data);

        // Sync groups
        if ($request->has('groups')) {
            $contact->groups()->sync($request->groups);
        }

        // Log activity
        $contact->logActivity('note', 'Contact created');

        return redirect()
            ->route('contacts.show', $contact)
            ->with('success', 'Contact created successfully!');
    }

    /**
     * Display the specified contact
     */
    public function show(Contact $contact)
    {
        $contact->load([
            'company',
            'groups',
            'notes' => fn($q) => $q->with('user')->latest(),
            'activities' => fn($q) => $q->with('user')->latest()->limit(20),
        ]);

        return view('contacts.show', compact('contact'));
    }

    /**
     * Show the form for editing the contact
     */
    public function edit(Contact $contact)
    {
        $companies = Company::forUser(auth()->id())->orderBy('name')->get();
        $groups = Group::forUser(auth()->id())->get();

        return view('contacts.edit', compact('contact', 'companies', 'groups'));
    }

    /**
     * Update the specified contact
     */
    public function update(UpdateContactRequest $request, Contact $contact)
    {
        $data = $request->validated();

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($contact->photo) {
                Storage::disk('public')->delete($contact->photo);
            }
            $data['photo'] = $request->file('photo')->store('contacts', 'public');
        }

        $contact->update($data);

        // Sync groups
        if ($request->has('groups')) {
            $contact->groups()->sync($request->groups);
        }

        return redirect()
            ->route('contacts.show', $contact)
            ->with('success', 'Contact updated successfully!');
    }

    /**
     * Remove the specified contact
     */
    public function destroy(Contact $contact)
    {
        // Delete photo
        if ($contact->photo) {
            Storage::disk('public')->delete($contact->photo);
        }

        $contact->delete();

        return redirect()
            ->route('contacts.index')
            ->with('success', 'Contact deleted successfully!');
    }

    /**
     * Add note to contact
     */
    public function addNote(Request $request, Contact $contact)
    {
        $this->authorize('update', $contact);

        $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $contact->notes()->create([
            'user_id' => auth()->id(),
            'body' => $request->body,
        ]);

        $contact->logActivity('note', 'Note added');

        return back()->with('success', 'Note added!');
    }

    /**
     * Add activity to contact
     */
    public function addActivity(Request $request, Contact $contact)
    {
        $this->authorize('update', $contact);

        $request->validate([
            'type' => 'required|in:call,email,meeting,note,task,other',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'scheduled_at' => 'nullable|date',
        ]);

        $contact->activities()->create([
            'user_id' => auth()->id(),
            ...$request->only(['type', 'title', 'description', 'scheduled_at']),
        ]);

        // Update last contacted
        if (in_array($request->type, ['call', 'email', 'meeting'])) {
            $contact->markAsContacted();
        }

        return back()->with('success', 'Activity logged!');
    }

    /**
     * Export contacts to CSV
     */
    public function export(Request $request)
    {
        $filename = 'contacts_' . date('Y-m-d_His') . '.csv';
        
        return Excel::download(
            new ContactsExport(auth()->id(), $request->all()),
            $filename
        );
    }

    /**
     * Import contacts from CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        Excel::import(new ContactsImport(auth()->id()), $request->file('file'));

        return back()->with('success', 'Contacts imported successfully!');
    }

    /**
     * Check for duplicate contacts
     */
    public function checkDuplicates(Request $request)
    {
        $duplicates = Contact::findDuplicates(
            $request->email,
            $request->phone
        );

        return response()->json([
            'has_duplicates' => $duplicates->isNotEmpty(),
            'duplicates' => $duplicates,
        ]);
    }
}
