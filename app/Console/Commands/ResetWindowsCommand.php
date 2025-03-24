<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\WindowController;
use Carbon\Carbon;

class ResetWindowsCommand extends Command
{
    protected $signature = 'windows:reset';
    protected $description = 'Auto reset windows based on scheduled time.';

    public function handle()
    {
        Log::info('🔄 Checking for scheduled resets...');

        try {
            $settings = DB::table('reset_settings')->first();

            if (!$settings || !$settings->auto_reset) {
                Log::info('❌ Auto reset is disabled.');
                return;
            }

            $currentTime = now();
            $resetTime = Carbon::parse($settings->reset_time);

            $currentDay = $currentTime->format('l'); // Full day name (Monday, Tuesday, etc.)
            $currentDate = $currentTime->format('Y-m-d'); // Full date

            // Debugging logs
            Log::debug("🕒 Current Time: {$currentTime->toDateTimeString()} | Reset Time: {$resetTime->toDateTimeString()}");
            Log::debug("📅 Reset Type: $settings->reset_type | Reset Day: $settings->reset_day | Reset Date: $settings->reset_date");

            $shouldReset = false;

            // ✅ If reset time has already passed, log and exit
            if ($currentTime->greaterThan($resetTime)) {
                Log::info('⏳ No schedule found or time passed.');
                return;
            }

            if ($settings->reset_type === 'Daily' && abs($currentTime->timestamp - $resetTime->timestamp) <= 60) {
                $shouldReset = true;
            } 
            elseif ($settings->reset_type === 'Weekly' && $currentTime->equalTo($resetTime) && $settings->reset_day === $currentDay) {
                $shouldReset = true;
            } 
            elseif ($settings->reset_type === 'Monthly' && $currentTime->equalTo($resetTime) && $settings->reset_date === $currentDate) {
                $shouldReset = true;
            }

            if ($shouldReset) {
                Log::info('✅ Running auto reset for windows...');
                
                try {
                    app(WindowController::class)->resetWindows();
                    Log::info('✅ Windows reset successfully.');
                } catch (\Exception $e) {
                    Log::error("❌ Error resetting windows: " . $e->getMessage());
                }
            } else {
                Log::debug('⏳ No scheduled reset at this time.');
            }
        } catch (\Exception $e) {
            Log::error("❌ Error running windows:reset command: " . $e->getMessage());
        }
    }
}
