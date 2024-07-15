@extends('layouts.app')

@section('title', $contact->full_name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="flex items-start justify-between mb-8">
        <div class="flex items-center">
            @if($contact->photo_url)
                <img src="{{ $contact->photo_url }}" alt="{{ $contact->full_name }}" 
                     class="h-20 w-20 rounded-full object-cover">
            @else
                <div class="h-20 w-20 rounded-full bg-blue-500 flex items-center justify-center text-white text-2xl font-bold">
                    {{ $contact->initials }}
                </div>
            @endif
            <div class="ml-6">
                <h1 class="text-2xl font-bold text-gray-900">{{ $contact->full_name }}</h1>
                @if($contact->job_title)
                    <p class="text-gray-600">{{ $contact->job_title }}
                        @if($contact->company)
                            at <a href="{{ route('companies.show', $contact->company) }}" class="text-blue-600">
                                {{ $contact->company->name }}
                            </a>
                        @endif
                    </p>
                @endif
                <div class="flex gap-2 mt-2">
                    <span class="px-3 py-1 text-sm rounded-full bg-green-100 text-green-800">
                        {{ ucfirst($contact->status) }}
                    </span>
                    @foreach($contact->groups as $group)
                        <span class="px-3 py-1 text-sm rounded-full" style="background-color: {{ $group->color }}20; color: {{ $group->color }}">
                            {{ $group->name }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('contacts.edit', $contact) }}" 
               class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                Edit
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Contact Info --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Contact Details --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
                <dl class="space-y-3">
                    @if($contact->email)
                        <div>
                            <dt class="text-sm text-gray-500">Email</dt>
                            <dd class="text-gray-900">
                                <a href="mailto:{{ $contact->email }}" class="text-blue-600 hover:underline">
                                    {{ $contact->email }}
                                </a>
                            </dd>
                        </div>
                    @endif
                    @if($contact->phone)
                        <div>
                            <dt class="text-sm text-gray-500">Phone</dt>
                            <dd class="text-gray-900">{{ $contact->phone }}</dd>
                        </div>
                    @endif
                    @if($contact->mobile)
                        <div>
                            <dt class="text-sm text-gray-500">Mobile</dt>
                            <dd class="text-gray-900">{{ $contact->mobile }}</dd>
                        </div>
                    @endif
                    @if($contact->full_address)
                        <div>
                            <dt class="text-sm text-gray-500">Address</dt>
                            <dd class="text-gray-900">{{ $contact->full_address }}</dd>
                        </div>
                    @endif
                    @if($contact->birthday)
                        <div>
                            <dt class="text-sm text-gray-500">Birthday</dt>
                            <dd class="text-gray-900">{{ $contact->birthday->format('F j, Y') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Social Links --}}
            @if($contact->linkedin || $contact->twitter || $contact->facebook)
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Social</h3>
                    <div class="space-y-2">
                        @if($contact->linkedin)
                            <a href="{{ $contact->linkedin }}" target="_blank" class="flex items-center text-blue-600 hover:underline">
                                LinkedIn
                            </a>
                        @endif
                        @if($contact->twitter)
                            <a href="https://twitter.com/{{ $contact->twitter }}" target="_blank" class="flex items-center text-blue-400 hover:underline">
                                @{{ $contact->twitter }}
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Activity & Notes --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Add Activity Form --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Log Activity</h3>
                <form action="{{ route('contacts.activities.store', $contact) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <select name="type" required class="rounded-lg border-gray-300">
                            <option value="call">📞 Call</option>
                            <option value="email">✉️ Email</option>
                            <option value="meeting">📅 Meeting</option>
                            <option value="note">📝 Note</option>
                            <option value="task">✅ Task</option>
                        </select>
                        <input type="text" name="title" placeholder="Title" required 
                               class="rounded-lg border-gray-300">
                    </div>
                    <textarea name="description" rows="2" placeholder="Description (optional)"
                              class="mt-4 w-full rounded-lg border-gray-300"></textarea>
                    <div class="mt-4 flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Log Activity
                        </button>
                    </div>
                </form>
            </div>

            {{-- Notes --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Notes</h3>
                <form action="{{ route('contacts.notes.store', $contact) }}" method="POST" class="mb-6">
                    @csrf
                    <textarea name="body" rows="3" placeholder="Add a note..." required
                              class="w-full rounded-lg border-gray-300"></textarea>
                    <div class="mt-2 flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm">
                            Add Note
                        </button>
                    </div>
                </form>
                
                <div class="space-y-4">
                    @forelse($contact->notes as $note)
                        <div class="border-l-4 border-gray-200 pl-4 py-2">
                            <p class="text-gray-700">{{ $note->body }}</p>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $note->user->name }} · {{ $note->created_at->diffForHumans() }}
                            </p>
                        </div>
                    @empty
                        <p class="text-gray-500">No notes yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Activity Timeline --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Activity Timeline</h3>
                <div class="space-y-4">
                    @forelse($contact->activities as $activity)
                        <div class="flex gap-4">
                            <div class="text-2xl">{{ $activity->icon }}</div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $activity->title }}</p>
                                @if($activity->description)
                                    <p class="text-gray-600 text-sm">{{ $activity->description }}</p>
                                @endif
                                <p class="text-sm text-gray-500">
                                    {{ $activity->user->name }} · {{ $activity->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500">No activities yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
