<?php

namespace Tests\Feature;

use App\Models\Registration;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminDashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_statistics_are_correct(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $workshopA = $this->workshop('Laravel', now()->addDays(2));
        $workshopB = $this->workshop('Vue', now()->addDays(3));

        $userOne = User::factory()->create(['role' => 'employee']);
        $userTwo = User::factory()->create(['role' => 'employee']);
        $userThree = User::factory()->create(['role' => 'employee']);
        $userFour = User::factory()->create(['role' => 'employee']);

        Registration::create([
            'user_id' => $userOne->id,
            'workshop_id' => $workshopA->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        Registration::create([
            'user_id' => $userTwo->id,
            'workshop_id' => $workshopA->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        Registration::create([
            'user_id' => $userThree->id,
            'workshop_id' => $workshopA->id,
            'status' => Registration::STATUS_WAITLISTED,
            'waitlist_position' => 1,
        ]);

        Registration::create([
            'user_id' => $userFour->id,
            'workshop_id' => $workshopB->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.workshops.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Workshops/Index')
            ->where('stats.total_workshops', 2)
            ->where('stats.total_confirmed_registrations', 3)
            ->where('stats.total_waitlisted_registrations', 1)
            ->where('stats.most_popular_workshop.id', $workshopA->id)
            ->where('stats.most_popular_workshop.title', $workshopA->title)
            ->where('stats.most_popular_workshop.confirmed_registrations_count', 2)
            ->has('registrationsPerWorkshop', 2)
            ->where('registrationsPerWorkshop.0.id', $workshopA->id)
            ->where('registrationsPerWorkshop.0.confirmed_registrations_count', 2)
            ->where('registrationsPerWorkshop.0.waitlisted_registrations_count', 1)
            ->where('registrationsPerWorkshop.1.id', $workshopB->id)
            ->where('registrationsPerWorkshop.1.confirmed_registrations_count', 1)
            ->where('registrationsPerWorkshop.1.waitlisted_registrations_count', 0)
        );
    }

    public function test_most_popular_workshop_is_null_when_no_confirmed_registrations_exist(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->workshop('No Registrations', now()->addDays(2));

        $response = $this->actingAs($admin)->get(route('admin.workshops.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Workshops/Index')
            ->where('stats.most_popular_workshop', null)
        );
    }

    private function workshop(string $title, \DateTimeInterface $start): Workshop
    {
        return Workshop::create([
            'title' => $title,
            'description' => 'Workshop description',
            'starts_at' => $start,
            'ends_at' => (clone $start)->modify('+2 hours'),
            'capacity' => 10,
        ]);
    }
}
