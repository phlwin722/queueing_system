<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\WindowController;

class ResetWindowsCommand extends Command
{
    protected $signature = 'windows:reset';
    protected $description = 'Auto reset windows based on scheduled time.';

    public function handle()
    {
        Log::info('Checking for scheduled resets...');

        // Get the reset settings
        $settings = DB::table('reset_settings')->first();

        if (!$settings || !$settings->auto_reset) {
            Log::info('Auto reset is disabled.');
            return;
        }

        $currentTime = now()->format('H:i');
        $currentDay  = now()->format('l');  // Example: "Monday"
        $currentDate = now()->day;          // Example: "15"

        if (
            ($settings->reset_type === 'Daily' && $settings->reset_time === $currentTime) ||
            ($settings->reset_type === 'Weekly' && $settings->reset_time === $currentTime && $settings->reset_day === $currentDay) ||
            ($settings->reset_type === 'Monthly' && $settings->reset_time === $currentTime && $settings->reset_date == $currentDate)
        ) {
            Log::info('Running auto reset for windows...');
            app(WindowController::class)->resetWindows();
        } else {
            Log::info('No scheduled reset for now.');
        }
    }
}
