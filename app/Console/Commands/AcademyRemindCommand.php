<?php

namespace App\Console\Commands;

use App\Mail\WorkshopReminderMail;
use App\Models\Registration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class AcademyRemindCommand extends Command
{
    protected $signature = 'academy:remind';

    protected $description = 'Send reminder emails to confirmed participants for workshops scheduled tomorrow';

    public function handle(): int
    {
        $tomorrowStart = now()->addDay()->startOfDay();
        $tomorrowEnd = now()->addDay()->endOfDay();

        $registrations = Registration::query()
            ->where('status', Registration::STATUS_CONFIRMED)
            ->whereHas('workshop', function ($query) use ($tomorrowStart, $tomorrowEnd): void {
                $query->whereBetween('starts_at', [$tomorrowStart, $tomorrowEnd]);
            })
            ->with(['user', 'workshop'])
            ->get();

        $sent = 0;

        foreach ($registrations as $registration) {
            if (! $registration->user || ! $registration->user->email || ! $registration->workshop) {
                continue;
            }

            Mail::to($registration->user->email)->send(
                new WorkshopReminderMail($registration->user->name, $registration->workshop)
            );

            $sent++;
        }

        $this->info("Sent {$sent} reminder email(s).");

        return self::SUCCESS;
    }
}
