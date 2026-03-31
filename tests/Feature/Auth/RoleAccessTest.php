<?php

namespace Tests\Feature\Auth;

use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_admin_is_redirected_to_admin_workshops_and_can_access_admin_pages(): void
    {
        $this->seed(DemoDataSeeder::class);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.workshops.index'));

        $adminPage = $this->get(route('admin.workshops.index'));
        $adminPage->assertOk();
    }

    public function test_seeded_employee_is_redirected_to_dashboard_and_can_access_employee_pages(): void
    {
        $this->seed(DemoDataSeeder::class);

        $response = $this->post('/login', [
            'email' => 'employee1@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));

        $employeePage = $this->get(route('dashboard'));
        $employeePage->assertOk();
    }

    public function test_seeded_employee_cannot_access_admin_pages(): void
    {
        $this->seed(DemoDataSeeder::class);

        $this->post('/login', [
            'email' => 'employee1@example.com',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->get(route('admin.workshops.index'))->assertForbidden();
    }
}
