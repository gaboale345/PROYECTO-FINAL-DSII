<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('scsp:backup-database')->everySixHours();
Schedule::command('scsp:merge-duplicates')->everyFiveMinutes();
Schedule::command('scsp:generate-predictions')->hourly();
Schedule::command('scsp:retrain-model')->weeklyOn(1, '3:00');
