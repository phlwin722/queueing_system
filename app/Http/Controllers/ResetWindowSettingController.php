<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ResetWindowSetting;

class ResetWindowSettingController extends Controller {
    // Fetch settings
    public function fetch() {
        $settings = ResetWindowSetting::first();
        return response()->json($settings ?? [
            'auto_reset' => false,
            'reset_minutes' => 0,
            'reset_days' => 0,
            'reset_weeks' => 0
        ]);
    }

    // Store or update settings
    public function store(Request $request) {
    \Log::info('Received Data:', $request->all());

    $validated = $request->validate([
        'auto_reset' => 'nullable|boolean',
        'reset_minutes' => 'nullable|integer|min:0',
        'reset_days' => 'nullable|integer|min:0',
        'reset_weeks' => 'nullable|integer|min:0',
    ]);

    // Convert null to false (para di mawala ang checkbox state)
    $validated['auto_reset'] = $validated['auto_reset'] ?? false;

    $settings = ResetWindowSetting::first();

    if ($settings) {
        $settings->update($validated);
    } else {
        $settings = ResetWindowSetting::create($validated);
    }

    return response()->json([
        'message' => 'Reset window settings saved successfully!',
        'data' => $settings // Send updated data
    ]);
}

}

