<?php

namespace Tests\Unit\Services;

use App\Models\Registration;
use App\Models\User;
use App\Models\Workshop;
use App\Services\RegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_waitlisted_registration_when_workshop_is_full(): void
    {
        $service = app(RegistrationService::class);

        $confirmedUser = User::factory()->create(['role' => 'employee']);
        $waitlistedUser = User::factory()->create(['role' => 'employee']);
        $workshop = $this->workshop(capacity: 1, startHour: 10, endHour: 12, daysFromNow: 2);

        Registration::create([
            'user_id' => $confirmedUser->id,
            'workshop_id' => $workshop->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        $result = $service->register($waitlistedUser, $workshop);

        $this->assertSame(Registration::STATUS_WAITLISTED, $result['status']);
        $this->assertSame(1, $result['waitlist_position']);
        $this->assertDatabaseHas('registrations', [
            'user_id' => $waitlistedUser->id,
            'workshop_id' => $workshop->id,
            'status' => Registration::STATUS_WAITLISTED,
            'waitlist_position' => 1,
        ]);
    }

    public function test_cancel_promotes_first_waitlisted_registration(): void
    {
        $service = app(RegistrationService::class);

        $confirmedUser = User::factory()->create(['role' => 'employee']);
        $firstWaitlisted = User::factory()->create(['role' => 'employee']);
        $secondWaitlisted = User::factory()->create(['role' => 'employee']);

        $workshop = $this->workshop(capacity: 1, startHour: 10, endHour: 12, daysFromNow: 2);

        Registration::create([
            'user_id' => $confirmedUser->id,
            'workshop_id' => $workshop->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        Registration::create([
            'user_id' => $firstWaitlisted->id,
            'workshop_id' => $workshop->id,
            'status' => Registration::STATUS_WAITLISTED,
            'waitlist_position' => 1,
        ]);

        Registration::create([
            'user_id' => $secondWaitlisted->id,
            'workshop_id' => $workshop->id,
            'status' => Registration::STATUS_WAITLISTED,
            'waitlist_position' => 2,
        ]);

        $result = $service->cancel($confirmedUser, $workshop);

        $this->assertTrue($result['cancelled']);
        $this->assertSame($firstWaitlisted->id, $result['promoted_user_id']);
        $this->assertDatabaseHas('registrations', [
            'user_id' => $firstWaitlisted->id,
            'workshop_id' => $workshop->id,
            'status' => Registration::STATUS_CONFIRMED,
            'waitlist_position' => null,
        ]);
        $this->assertDatabaseHas('registrations', [
            'user_id' => $secondWaitlisted->id,
            'workshop_id' => $workshop->id,
            'status' => Registration::STATUS_WAITLISTED,
            'waitlist_position' => 2,
        ]);
    }

    public function test_register_blocks_overlapping_workshop_when_user_is_already_confirmed_elsewhere(): void
    {
        $service = app(RegistrationService::class);

        $user = User::factory()->create(['role' => 'employee']);

        $firstWorkshop = $this->workshop(capacity: 5, startHour: 10, endHour: 12, daysFromNow: 2);
        $overlapWorkshop = $this->workshop(capacity: 5, startHour: 11, endHour: 13, daysFromNow: 2);

        Registration::create([
            'user_id' => $user->id,
            'workshop_id' => $firstWorkshop->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        $result = $service->register($user, $overlapWorkshop);

        $this->assertSame('You are already confirmed for an overlapping workshop.', $result['error']);
        $this->assertDatabaseMissing('registrations', [
            'user_id' => $user->id,
            'workshop_id' => $overlapWorkshop->id,
        ]);
    }

    private function workshop(int $capacity, int $startHour, int $endHour, int $daysFromNow): Workshop
    {
        return Workshop::create([
            'title' => 'Workshop '.fake()->unique()->word(),
            'description' => 'Description',
            'starts_at' => now()->addDays($daysFromNow)->setTime($startHour, 0),
            'ends_at' => now()->addDays($daysFromNow)->setTime($endHour, 0),
            'capacity' => $capacity,
        ]);
    }
}
