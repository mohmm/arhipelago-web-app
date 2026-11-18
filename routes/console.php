<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| Nothing below runs unless the server calls `schedule:run` every minute:
|
|     * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
|
| Times are stated in Europe/Bucharest so that "overnight" stays overnight
| through both halves of the year.
|
*/

/*
 * Drains the database queue on hosts that run no long-lived worker. A worker
 * started by a process manager makes this a no-op: the queue is already empty.
 */
Schedule::command('queue:work --stop-when-empty --max-time=55 --tries=3')
    ->everyMinute()
    ->withoutOverlapping(10);

Schedule::command('auth:clear-resets')
    ->dailyAt('04:00')
    ->timezone('Europe/Bucharest');

Schedule::command('sanctum:prune-expired --hours=24')
    ->dailyAt('04:10')
    ->timezone('Europe/Bucharest');

/*
 * Deletes what the Privacy Policy says is kept for a limited time: conversations
 * with Maya nobody has written in for a year (config/maya.php).
 */
Schedule::command('model:prune')
    ->dailyAt('04:20')
    ->timezone('Europe/Bucharest');
