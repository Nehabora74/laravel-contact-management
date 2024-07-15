<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Group;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo user
        $user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create groups
        $groups = collect([
            ['name' => 'VIP', 'color' => '#EF4444'],
            ['name' => 'Partners', 'color' => '#10B981'],
            ['name' => 'Vendors', 'color' => '#3B82F6'],
            ['name' => 'Friends', 'color' => '#8B5CF6'],
            ['name' => 'Family', 'color' => '#F59E0B'],
        ])->map(fn($g) => Group::create([...$g, 'user_id' => $user->id]));

        // Create companies
        $companies = collect([
            ['name' => 'Acme Corporation', 'industry' => 'Technology', 'website' => 'https://acme.com'],
            ['name' => 'Global Industries', 'industry' => 'Manufacturing', 'website' => 'https://global.com'],
            ['name' => 'StartupXYZ', 'industry' => 'SaaS', 'website' => 'https://startupxyz.io'],
            ['name' => 'Design Studio', 'industry' => 'Creative', 'website' => 'https://designstudio.com'],
            ['name' => 'Finance Corp', 'industry' => 'Finance', 'website' => 'https://financecorp.com'],
        ])->map(fn($c) => Company::create([
            ...$c,
            'user_id' => $user->id,
            'email' => 'info@' . strtolower(str_replace(' ', '', $c['name'])) . '.com',
            'phone' => '+1-555-' . rand(100, 999) . '-' . rand(1000, 9999),
        ]));

        // Create contacts
        $contacts = [
            ['first_name' => 'John', 'last_name' => 'Doe', 'job_title' => 'CEO', 'status' => 'customer'],
            ['first_name' => 'Jane', 'last_name' => 'Smith', 'job_title' => 'CTO', 'status' => 'customer'],
            ['first_name' => 'Michael', 'last_name' => 'Johnson', 'job_title' => 'Marketing Director', 'status' => 'lead'],
            ['first_name' => 'Sarah', 'last_name' => 'Williams', 'job_title' => 'Product Manager', 'status' => 'active'],
            ['first_name' => 'David', 'last_name' => 'Brown', 'job_title' => 'Sales Manager', 'status' => 'customer'],
            ['first_name' => 'Emily', 'last_name' => 'Davis', 'job_title' => 'Designer', 'status' => 'active'],
            ['first_name' => 'Robert', 'last_name' => 'Miller', 'job_title' => 'Developer', 'status' => 'lead'],
            ['first_name' => 'Lisa', 'last_name' => 'Anderson', 'job_title' => 'HR Manager', 'status' => 'active'],
            ['first_name' => 'James', 'last_name' => 'Taylor', 'job_title' => 'Consultant', 'status' => 'customer'],
            ['first_name' => 'Maria', 'last_name' => 'Garcia', 'job_title' => 'Accountant', 'status' => 'active'],
        ];

        foreach ($contacts as $contactData) {
            $contact = Contact::create([
                ...$contactData,
                'user_id' => $user->id,
                'company_id' => $companies->random()->id,
                'email' => strtolower($contactData['first_name']) . '.' . strtolower($contactData['last_name']) . '@example.com',
                'phone' => '+1-555-' . rand(100, 999) . '-' . rand(1000, 9999),
                'city' => collect(['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix'])->random(),
                'country' => 'USA',
            ]);

            // Attach random groups
            $contact->groups()->attach($groups->random(rand(1, 3))->pluck('id'));

            // Add some notes
            $contact->notes()->create([
                'user_id' => $user->id,
                'body' => 'Initial contact made via LinkedIn.',
            ]);

            // Add some activities
            $contact->activities()->create([
                'user_id' => $user->id,
                'type' => collect(['call', 'email', 'meeting'])->random(),
                'title' => 'Initial contact',
                'description' => 'Discussed potential partnership opportunities.',
            ]);
        }
    }
}
