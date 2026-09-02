<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:send-fee-reminders')->dailyAt('08:00');
Schedule::command('app:send-canteen-window-notifications open')->dailyAt('18:00');
Schedule::command('app:send-canteen-window-notifications close')->dailyAt('20:00');
Schedule::command('app:send-library-overdue-reminders')->dailyAt('08:30');
