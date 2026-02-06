<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| Define the application's command schedule. These commands will be run
| by the Laravel scheduler when `php artisan schedule:run` is executed.
|
*/

// Send rent due notifications daily at 8:00 AM
Schedule::command('notifications:rent-due')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/notifications.log'));

// Send late payment notifications daily at 9:00 AM
Schedule::command('notifications:late-payments')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/notifications.log'));
