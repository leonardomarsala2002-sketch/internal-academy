<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Workshop;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => 'password',
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'employee1@example.com'],
            [
                'name' => 'Employee One',
                'password' => 'password',
                'role' => 'employee',
            ]
        );

        User::updateOrCreate(
            ['email' => 'employee2@example.com'],
            [
                'name' => 'Employee Two',
                'password' => 'password',
                'role' => 'employee',
            ]
        );

        Workshop::updateOrCreate(
            ['title' => 'Laravel Basics'],
            [
                'description' => 'Introductory workshop on Laravel fundamentals.',
                'starts_at' => Carbon::now()->addDays(2)->setTime(10, 0),
                'ends_at' => Carbon::now()->addDays(2)->setTime(12, 0),
                'capacity' => 2,
            ]
        );

        Workshop::updateOrCreate(
            ['title' => 'Vue with Inertia'],
            [
                'description' => 'Learn how to build smooth apps with Vue and Inertia.',
                'starts_at' => Carbon::now()->addDays(3)->setTime(15, 0),
                'ends_at' => Carbon::now()->addDays(3)->setTime(17, 0),
                'capacity' => 3,
            ]
        );
    }
}