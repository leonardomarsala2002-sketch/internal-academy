<?php

namespace Tests\Unit\Services;

use App\Models\Registration;
use App\Models\User;
use App\Models\Workshop;
use App\Services\AdminWorkshopStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWorkshopStatsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_returns_correct_totals_and_most_popular_workshop(): void
    {
        $service = app(AdminWorkshopStatsService::class);

        $workshopA = $this->workshop('A', now()->addDays(2), capacity: 3);
        $workshopB = $this->workshop('B', now()->addDays(3), capacity: 4);

        $u1 = User::factory()->create(['role' => 'employee']);
        $u2 = User::factory()->create(['role' => 'employee']);
        $u3 = User::factory()->create(['role' => 'employee']);

        Registration::create([
            'user_id' => $u1->id,
            'workshop_id' => $workshopA->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        Registration::create([
            'user_id' => $u2->id,
            'workshop_id' => $workshopA->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        Registration::create([
            'user_id' => $u3->id,
            'workshop_id' => $workshopB->id,
            'status' => Registration::STATUS_WAITLISTED,
            'waitlist_position' => 1,
        ]);

        $summary = $service->summary();

        $this->assertSame(2, $summary['total_workshops']);
        $this->assertSame(2, $summary['total_confirmed_registrations']);
        $this->assertSame(1, $summary['total_waitlisted_registrations']);
        $this->assertSame($workshopA->id, $summary['most_popular_workshop']['id']);
        $this->assertSame(2, $summary['most_popular_workshop']['confirmed_registrations_count']);
    }

    public function test_registrations_per_workshop_includes_counts_and_seats_left(): void
    {
        $service = app(AdminWorkshopStatsService::class);

        $workshop = $this->workshop('Stats Workshop', now()->addDays(2), capacity: 2);

        $u1 = User::factory()->create(['role' => 'employee']);
        $u2 = User::factory()->create(['role' => 'employee']);
        $u3 = User::factory()->create(['role' => 'employee']);

        Registration::create([
            'user_id' => $u1->id,
            'workshop_id' => $workshop->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        Registration::create([
            'user_id' => $u2->id,
            'workshop_id' => $workshop->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        Registration::create([
            'user_id' => $u3->id,
            'workshop_id' => $workshop->id,
            'status' => Registration::STATUS_WAITLISTED,
            'waitlist_position' => 1,
        ]);

        $rows = $service->registrationsPerWorkshop();

        $this->assertCount(1, $rows);
        $this->assertSame($workshop->id, $rows[0]['id']);
        $this->assertSame(2, $rows[0]['confirmed_registrations_count']);
        $this->assertSame(1, $rows[0]['waitlisted_registrations_count']);
        $this->assertSame(0, $rows[0]['seats_left']);
    }

    private function workshop(string $title, \DateTimeInterface $startsAt, int $capacity): Workshop
    {
        return Workshop::create([
            'title' => $title,
            'description' => 'Description',
            'starts_at' => $startsAt,
            'ends_at' => (clone $startsAt)->modify('+2 hours'),
            'capacity' => $capacity,
        ]);
    }
}
