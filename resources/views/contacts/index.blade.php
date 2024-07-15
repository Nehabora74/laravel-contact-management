@extends('layouts.app')

@section('title', 'Contacts')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Contacts</h1>
        <div class="mt-4 sm:mt-0 flex gap-3">
            <a href="{{ route('contacts.export', request()->query()) }}" 
               class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                Export CSV
            </a>
            <a href="{{ route('contacts.create') }}" 
               class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                + Add Contact
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="mb-6 bg-white p-4 rounded-lg shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search contacts..." class="rounded-lg border-gray-300">
            
            <select name="status" class="rounded-lg border-gray-300">
                <option value="">All Statuses</option>
                @foreach(['active', 'inactive', 'lead', 'customer'] as $status)
                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>
            
            <select name="company" class="rounded-lg border-gray-300">
                <option value="">All Companies</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ request('company') == $company->id ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
            
            <select name="group" class="rounded-lg border-gray-300">
                <option value="">All Groups</option>
                @foreach($groups as $group)
                    <option value="{{ $group->id }}" {{ request('group') == $group->id ? 'selected' : '' }}>
                        {{ $group->name }} ({{ $group->contacts_count }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mt-4 flex justify-end gap-2">
            <a href="{{ route('contacts.index') }}" class="px-4 py-2 text-sm text-gray-600">Clear</a>
            <button type="submit" class="px-4 py-2 text-sm bg-gray-800 text-white rounded-lg">Filter</button>
        </div>
    </form>

    {{-- Contacts Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email / Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Groups</th>
                    <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($contacts as $contact)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-medium">
                                    {{ $contact->initials }}
                                </div>
                                <div class="ml-4">
                                    <a href="{{ route('contacts.show', $contact) }}" class="text-sm font-medium text-gray-900 hover:text-blue-600">
                                        {{ $contact->full_name }}
                                    </a>
                                    @if($contact->job_title)
                                        <div class="text-sm text-gray-500">{{ $contact->job_title }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $contact->company->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $contact->email ?? '-' }}</div>
                            <div class="text-sm text-gray-500">{{ $contact->phone }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                {{ ucfirst($contact->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @foreach($contact->groups->take(2) as $group)
                                <span class="px-2 py-1 text-xs rounded" style="background-color: {{ $group->color }}20">
                                    {{ $group->name }}
                                </span>
                            @endforeach
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('contacts.edit', $contact) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            No contacts found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $contacts->links() }}</div>
</div>
@endsection
