<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\Contact::updateOrCreate(
            ['email' => 'john@example.com'],
            [
                'name' => 'John Doe',
                'phone' => '+1234567890',
                'gender' => 'male',
                'custom_fields' => ['company' => 'Tech Corp', 'birthday' => '1990-01-15']
            ]
        );

        \App\Models\Contact::updateOrCreate(
            ['email' => 'jane@example.com'],
            [
                'name' => 'Jane Smith',
                'phone' => '+1234567891',
                'gender' => 'female',
                'custom_fields' => ['company' => 'Design Studio', 'address' => '123 Main St']
            ]
        );

        \App\Models\Contact::updateOrCreate(
            ['email' => 'alex@example.com'],
            [
                'name' => 'Alex Johnson',
                'phone' => '+1234567892',
                'gender' => 'other',
                'custom_fields' => ['company' => 'Startup Inc', 'notes' => 'VIP Client']
            ]
        );

        \App\Models\Contact::updateOrCreate(
            ['email' => 'robert@example.com'],
            [
                'name' => 'Robert Johnson',
                'phone' => '+1234567893',
                'gender' => 'male',
                'custom_fields' => ['company' => 'Johnson & Associates', 'position' => 'Lawyer']
            ]
        );

        \App\Models\Contact::updateOrCreate(
            ['email' => 'bob@example.com'],
            [
                'name' => 'Bob Johnson',
                'phone' => '+1234567894',
                'gender' => 'male',
                'custom_fields' => ['firm' => 'Legal Partners', 'specialization' => 'Corporate Law']
            ]
        );
    }
}
