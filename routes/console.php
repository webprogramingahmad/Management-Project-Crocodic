<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('sdm:reset-not-ready')->dailyAt('00:00');
Schedule::command('sdm:health-check --warn-threshold=90')->dailyAt('00:10');
