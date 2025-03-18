<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\ResetWindowSetting;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
{
    $settings = ResetWindowSetting::first();

    if ($settings && $settings->auto_reset) {
        if ($settings->reset_minutes > 0) {
            $schedule->command('reset:personnel')->everyMinute();
        }
        if ($settings->reset_days > 0) {
            $schedule->command('reset:personnel')->daily();
        }
        if ($settings->reset_weeks > 0) {
            $schedule->command('reset:personnel')->weekly();
        }
    }
}


    /**
     * Compute the cron expression based on reset settings.
     */
    protected function computeCronExpression($settings): string
    {
        if (!$settings || !$settings->auto_reset) {
            return '* * * * *'; // Default every minute kung walang settings
        }

        $intervalMinutes = ($settings->reset_minutes ?? 0) +
                           ($settings->reset_days ?? 0) * 1440 +
                           ($settings->reset_weeks ?? 0) * 10080;

        if ($intervalMinutes <= 0) {
            return '* * * * *'; // Default every minute kung 0
        }

        return "*/$intervalMinutes * * * *"; // Run based on computed interval
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
