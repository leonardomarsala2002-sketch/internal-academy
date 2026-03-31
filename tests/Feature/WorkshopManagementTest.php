<?php

namespace Tests\Feature;

use App\Models\Registration;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WorkshopManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_workshop_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.workshops.index'));

        $response->assertOk();
    }

    public function test_employee_cannot_access_admin_workshop_management(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($employee)->get(route('admin.workshops.index'));

        $response->assertForbidden();
    }

    public function test_admin_cannot_access_employee_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertForbidden();
    }

    public function test_employee_can_register_to_a_workshop_if_seats_are_available(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $workshop = $this->futureWorkshop(capacity: 2);

        $response = $this->actingAs($employee)->post(route('workshops.register', $workshop));

        $response->assertRedirect();
        $this->assertDatabaseHas('registrations', [
            'user_id' => $employee->id,
            'workshop_id' => $workshop->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);
    }

    public function test_employee_cannot_register_twice(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $workshop = $this->futureWorkshop(capacity: 2);

        Registration::create([
            'user_id' => $employee->id,
            'workshop_id' => $workshop->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($employee)->post(route('workshops.register', $workshop));

        $response->assertRedirect();
        $response->assertSessionHasErrors('registration');
        $this->assertDatabaseCount('registrations', 1);
    }

    public function test_employee_can_cancel_registration(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $workshop = $this->futureWorkshop(capacity: 2);

        Registration::create([
            'user_id' => $employee->id,
            'workshop_id' => $workshop->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($employee)->delete(route('workshops.unregister', $workshop));

        $response->assertRedirect();
        $this->assertDatabaseMissing('registrations', [
            'user_id' => $employee->id,
            'workshop_id' => $workshop->id,
        ]);
    }

    public function test_employee_joins_waiting_list_when_workshop_is_full(): void
    {
        $confirmedEmployee = User::factory()->create(['role' => 'employee']);
        $waitlistedEmployee = User::factory()->create(['role' => 'employee']);
        $workshop = $this->futureWorkshop(capacity: 1);

        Registration::create([
            'user_id' => $confirmedEmployee->id,
            'workshop_id' => $workshop->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($waitlistedEmployee)->post(route('workshops.register', $workshop));

        $response->assertRedirect();
        $this->assertDatabaseHas('registrations', [
            'user_id' => $waitlistedEmployee->id,
            'workshop_id' => $workshop->id,
            'status' => Registration::STATUS_WAITLISTED,
            'waitlist_position' => 1,
        ]);
    }

    public function test_cancelling_confirmed_registration_promotes_first_waitlisted_user(): void
    {
        $confirmedEmployee = User::factory()->create(['role' => 'employee']);
        $firstWaitlistedEmployee = User::factory()->create(['role' => 'employee']);
        $secondWaitlistedEmployee = User::factory()->create(['role' => 'employee']);
        $workshop = $this->futureWorkshop(capacity: 1);

        Registration::create([
            'user_id' => $confirmedEmployee->id,
            'workshop_id' => $workshop->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        Registration::create([
            'user_id' => $firstWaitlistedEmployee->id,
            'workshop_id' => $workshop->id,
            'status' => Registration::STATUS_WAITLISTED,
            'waitlist_position' => 1,
        ]);

        Registration::create([
            'user_id' => $secondWaitlistedEmployee->id,
            'workshop_id' => $workshop->id,
            'status' => Registration::STATUS_WAITLISTED,
            'waitlist_position' => 2,
        ]);

        $response = $this->actingAs($confirmedEmployee)->delete(route('workshops.unregister', $workshop));

        $response->assertRedirect();
        $this->assertDatabaseHas('registrations', [
            'user_id' => $firstWaitlistedEmployee->id,
            'workshop_id' => $workshop->id,
            'status' => Registration::STATUS_CONFIRMED,
            'waitlist_position' => null,
        ]);
        $this->assertDatabaseHas('registrations', [
            'user_id' => $secondWaitlistedEmployee->id,
            'workshop_id' => $workshop->id,
            'status' => Registration::STATUS_WAITLISTED,
            'waitlist_position' => 2,
        ]);
    }

    public function test_overlap_prevents_confirmed_registration(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $confirmedWorkshop = $this->futureWorkshop(capacity: 3, startsAt: now()->addDays(2)->setTime(10, 0), endsAt: now()->addDays(2)->setTime(12, 0));
        $overlappingWorkshop = $this->futureWorkshop(capacity: 3, startsAt: now()->addDays(2)->setTime(11, 0), endsAt: now()->addDays(2)->setTime(13, 0));

        Registration::create([
            'user_id' => $employee->id,
            'workshop_id' => $confirmedWorkshop->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($employee)->post(route('workshops.register', $overlappingWorkshop));

        $response->assertRedirect();
        $response->assertSessionHasErrors('registration');
        $this->assertDatabaseMissing('registrations', [
            'user_id' => $employee->id,
            'workshop_id' => $overlappingWorkshop->id,
        ]);
    }

    public function test_overlap_prevents_waitlist_joining(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $otherEmployee = User::factory()->create(['role' => 'employee']);

        $confirmedWorkshop = $this->futureWorkshop(capacity: 3, startsAt: now()->addDays(2)->setTime(10, 0), endsAt: now()->addDays(2)->setTime(12, 0));
        $overlappingFullWorkshop = $this->futureWorkshop(capacity: 1, startsAt: now()->addDays(2)->setTime(11, 0), endsAt: now()->addDays(2)->setTime(13, 0));

        Registration::create([
            'user_id' => $employee->id,
            'workshop_id' => $confirmedWorkshop->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        Registration::create([
            'user_id' => $otherEmployee->id,
            'workshop_id' => $overlappingFullWorkshop->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($employee)->post(route('workshops.register', $overlappingFullWorkshop));

        $response->assertRedirect();
        $response->assertSessionHasErrors('registration');
        $this->assertDatabaseMissing('registrations', [
            'user_id' => $employee->id,
            'workshop_id' => $overlappingFullWorkshop->id,
            'status' => Registration::STATUS_WAITLISTED,
        ]);
    }

    public function test_employee_dashboard_shows_confirmed_waitlisted_and_full_states(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $otherEmployee = User::factory()->create(['role' => 'employee']);

        $confirmedWorkshop = $this->futureWorkshop(capacity: 2, daysFromNow: 2);
        $waitlistWorkshop = $this->futureWorkshop(capacity: 1, daysFromNow: 3);
        $fullWorkshop = $this->futureWorkshop(capacity: 1, daysFromNow: 4);

        Registration::create([
            'user_id' => $employee->id,
            'workshop_id' => $confirmedWorkshop->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        Registration::create([
            'user_id' => $otherEmployee->id,
            'workshop_id' => $waitlistWorkshop->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        Registration::create([
            'user_id' => $employee->id,
            'workshop_id' => $waitlistWorkshop->id,
            'status' => Registration::STATUS_WAITLISTED,
            'waitlist_position' => 1,
        ]);

        Registration::create([
            'user_id' => $otherEmployee->id,
            'workshop_id' => $fullWorkshop->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($employee)->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Employee/Dashboard')
            ->has('workshops', 3)
            ->where('workshops.0.id', $confirmedWorkshop->id)
            ->where('workshops.0.registration_status', Registration::STATUS_CONFIRMED)
            ->where('workshops.0.state', 'confirmed')
            ->where('workshops.1.id', $waitlistWorkshop->id)
            ->where('workshops.1.registration_status', Registration::STATUS_WAITLISTED)
            ->where('workshops.1.state', 'waitlisted')
            ->where('workshops.2.id', $fullWorkshop->id)
            ->where('workshops.2.registration_status', null)
            ->where('workshops.2.state', 'full')
        );
    }

    private function futureWorkshop(
        int $capacity,
        int $daysFromNow = 2,
        ?\DateTimeInterface $startsAt = null,
        ?\DateTimeInterface $endsAt = null,
    ): Workshop {
        return Workshop::create([
            'title' => 'Workshop '.fake()->unique()->word(),
            'description' => 'Workshop description',
            'starts_at' => $startsAt ?? now()->addDays($daysFromNow)->setTime(10, 0),
            'ends_at' => $endsAt ?? now()->addDays($daysFromNow)->setTime(12, 0),
            'capacity' => $capacity,
        ]);
    }
}
