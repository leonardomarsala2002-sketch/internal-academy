<?php

namespace Tests\Feature;

use App\Mail\WorkshopReminderMail;
use App\Models\Registration;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AcademyRemindCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_sends_reminders_only_to_confirmed_participants_of_tomorrows_workshops(): void
    {
        Mail::fake();

        $tomorrowWorkshop = Workshop::create([
            'title' => 'Tomorrow Workshop',
            'description' => 'Description',
            'starts_at' => now()->addDay()->setTime(10, 0),
            'ends_at' => now()->addDay()->setTime(12, 0),
            'capacity' => 5,
        ]);

        $futureWorkshop = Workshop::create([
            'title' => 'Future Workshop',
            'description' => 'Description',
            'starts_at' => now()->addDays(3)->setTime(10, 0),
            'ends_at' => now()->addDays(3)->setTime(12, 0),
            'capacity' => 5,
        ]);

        $confirmedUser = User::factory()->create(['role' => 'employee']);
        $waitlistedUser = User::factory()->create(['role' => 'employee']);
        $futureUser = User::factory()->create(['role' => 'employee']);

        Registration::create([
            'user_id' => $confirmedUser->id,
            'workshop_id' => $tomorrowWorkshop->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        Registration::create([
            'user_id' => $waitlistedUser->id,
            'workshop_id' => $tomorrowWorkshop->id,
            'status' => Registration::STATUS_WAITLISTED,
            'waitlist_position' => 1,
        ]);

        Registration::create([
            'user_id' => $futureUser->id,
            'workshop_id' => $futureWorkshop->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        Artisan::call('academy:remind');

        Mail::assertSent(WorkshopReminderMail::class, 1);
        Mail::assertSent(WorkshopReminderMail::class, function (WorkshopReminderMail $mail) use ($confirmedUser, $tomorrowWorkshop) {
            return $mail->hasTo($confirmedUser->email)
                && $mail->workshop->is($tomorrowWorkshop);
        });
    }

    public function test_command_includes_tomorrows_day_boundaries_only(): void
    {
        Mail::fake();

        $tomorrowStart = now()->addDay()->startOfDay();
        $tomorrowEnd = now()->addDay()->endOfDay();

        $boundaryStartWorkshop = Workshop::create([
            'title' => 'Boundary Start Workshop',
            'description' => 'Description',
            'starts_at' => $tomorrowStart,
            'ends_at' => (clone $tomorrowStart)->modify('+1 hour'),
            'capacity' => 3,
        ]);

        $boundaryEndWorkshop = Workshop::create([
            'title' => 'Boundary End Workshop',
            'description' => 'Description',
            'starts_at' => $tomorrowEnd,
            'ends_at' => (clone $tomorrowEnd)->modify('+1 hour'),
            'capacity' => 3,
        ]);

        $todayWorkshop = Workshop::create([
            'title' => 'Today Workshop',
            'description' => 'Description',
            'starts_at' => now()->startOfDay()->setTime(23, 59),
            'ends_at' => now()->addDay()->startOfDay()->setTime(0, 59),
            'capacity' => 3,
        ]);

        $futureWorkshop = Workshop::create([
            'title' => 'Future Workshop',
            'description' => 'Description',
            'starts_at' => now()->addDays(2)->startOfDay(),
            'ends_at' => now()->addDays(2)->startOfDay()->setTime(1, 0),
            'capacity' => 3,
        ]);

        $startUser = User::factory()->create(['role' => 'employee']);
        $endUser = User::factory()->create(['role' => 'employee']);
        $todayUser = User::factory()->create(['role' => 'employee']);
        $futureUser = User::factory()->create(['role' => 'employee']);

        Registration::create([
            'user_id' => $startUser->id,
            'workshop_id' => $boundaryStartWorkshop->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        Registration::create([
            'user_id' => $endUser->id,
            'workshop_id' => $boundaryEndWorkshop->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        Registration::create([
            'user_id' => $todayUser->id,
            'workshop_id' => $todayWorkshop->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        Registration::create([
            'user_id' => $futureUser->id,
            'workshop_id' => $futureWorkshop->id,
            'status' => Registration::STATUS_CONFIRMED,
        ]);

        Artisan::call('academy:remind');

        Mail::assertSent(WorkshopReminderMail::class, 2);
        Mail::assertSent(WorkshopReminderMail::class, fn (WorkshopReminderMail $mail) => $mail->hasTo($startUser->email));
        Mail::assertSent(WorkshopReminderMail::class, fn (WorkshopReminderMail $mail) => $mail->hasTo($endUser->email));
    }
}
