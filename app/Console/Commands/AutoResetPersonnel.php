<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Window;
use App\Models\ResetWindowSetting;

class AutoResetPersonnel extends Command
{
    protected $signature = 'reset:personnel';
    protected $description = 'Automatically unassign personnel from windows based on reset settings';

    public function handle()
{
    $settings = ResetWindowSetting::first();

    if (!$settings || !$settings->auto_reset) {
        $this->info('Auto-reset is disabled. Skipping...');
        return;
    }

    $lastReset = $settings->last_reset_at ?? now()->subMinutes($settings->reset_minutes + 1);
    $nextReset = $lastReset
        ->addMinutes($settings->reset_minutes ?? 0)
        ->addDays($settings->reset_days ?? 0)
        ->addWeeks($settings->reset_weeks ?? 0);

    if (now()->lt($nextReset)) {
        $this->info('Not yet time for reset. Next reset at: ' . $nextReset);
        return;
    }

    // Archive Windows before resetting
    Window::whereNotNull('teller_id')->each(function ($window) {
        \DB::table('window_archives')->insert([
            'window_name' => $window->window_name,
            'type_id' => $window->type_id,
            'teller_id' => $window->teller_id,
            'archived_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    });

    // Reset Windows (Remove `teller_id`)
    Window::whereNotNull('teller_id')->update(['teller_id' => null]);

    // Update last reset time
    $settings->update(['last_reset_at' => now()]);

    $this->info('Windows personnel reset successfully. Next reset at: ' . now()->addMinutes($settings->reset_minutes)->addDays($settings->reset_days)->addWeeks($settings->reset_weeks));
}

}
