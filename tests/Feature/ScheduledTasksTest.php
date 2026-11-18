<?php

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;

/**
 * Every email in the application is queued, and on a host without a
 * long-lived worker the scheduler is what sends them. Silent when absent, so
 * asserted rather than assumed.
 */
function scheduledEvent(string $command): Event
{
    $event = collect(app(Schedule::class)->events())
        ->first(fn (Event $item): bool => str_contains((string) $item->command, $command));

    expect($event)->not->toBeNull("The schedule has no `{$command}` entry.");

    return $event;
}

it('drains the queue every minute and gives up its lock quickly', function () {
    $event = scheduledEvent('queue:work');

    // Laravel's default lock is a day: one killed worker would silence every
    // email until tomorrow. Ten minutes is well beyond any healthy run.
    expect($event->expression)->toBe('* * * * *')
        ->and($event->withoutOverlapping)->toBeTrue()
        ->and($event->expiresAt)->toBe(10);
});

it('clears expired password reset tokens every night', function () {
    expect(scheduledEvent('auth:clear-resets')->expression)->toBe('0 4 * * *');
});

it('prunes expired app sign-in tokens every night', function () {
    expect(scheduledEvent('sanctum:prune-expired')->expression)->toBe('10 4 * * *');
});

it('deletes old conversations with Maya every night', function () {
    expect(scheduledEvent('model:prune')->expression)->toBe('20 4 * * *');
});

it('schedules nothing from the old website', function () {
    $commands = collect(app(Schedule::class)->events())->map(fn (Event $event): string => (string) $event->command);

    expect($commands)->toHaveCount(4);
});
