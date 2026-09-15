<?php

namespace App\Console\Commands;

use App\Notifications\EventReminderNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('app:send-event-reminders')]
#[Description('Sends notifications to attendees for upcoming events')]
class SendEventReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {   
        $events = \App\Models\Event::with('attendees.user')->whereBetween('start_time', [now(), now()->addDay()])
            ->get();
            
        $eventCount = $events->count();
        $eventLabel = Str::plural('event', $eventCount);
        $this->info("Found {$eventCount} upcoming {$eventLabel}.");

        // $events->each(fn ($event) => $event->attendees->each(
        //     (fn ($attendee) => $this->info("Sending reminder to {$attendee->user->email} for event {$event->name}."))
        // ));
        $events->each(fn ($event) => $event->attendees->each(
            (fn ($attendee) => $attendee->user->notify(new EventReminderNotification($event)))
        ));

    }
}
