<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResetSettingController extends Controller
{
    // Fetch settings
    public function fetchSettings()
    {
        $settings = DB::table('reset_settings')->first();
        return response()->json($settings);
    }

    // Save settings
public function saveSettings(Request $request)
{
    Log::info('Received Data:', $request->all()); // Debugging log

    $validated = $request->validate([
        'auto_reset' => 'required|boolean',
        'reset_type' => 'nullable|string|in:Daily,Weekly,Monthly',
        'reset_time' => 'nullable|date_format:H:i', // Laravel validation (no seconds)
        'reset_day' => 'nullable|string',
        'reset_date' => 'nullable|date_format:Y-m-d', // Changed validation to accept full date
    ]);

    Log::info('Validated Data:', $validated);

    // Ensure `reset_time` is stored in HH:MM:SS format
    if ($validated['reset_time']) {
        // Kunin ang kasalukuyang seconds at idagdag sa reset_time
        $currentSeconds = now()->format('s');
        $validated['reset_time'] = $validated['reset_time'] . ':' . $currentSeconds;
    }
    

    // Convert full date (YYYY-MM-DD) to day only (1-31)
    if ($validated['reset_date']) {
        $validated['reset_date'] = date('Y-m-d', strtotime($validated['reset_date']));
    }
    

    // Ensure only one row exists
    $settings = DB::table('reset_settings')->first();

    // If auto_reset is false, clear other fields
    if (!$validated['auto_reset']) {
        $validated['reset_type'] = null;
        $validated['reset_time'] = null;
        $validated['reset_day'] = null;
        $validated['reset_date'] = null;
    }

    // Prepare data for insertion/update
    $data = [
        'auto_reset' => $validated['auto_reset'],
        'reset_type' => $validated['reset_type'],
        'reset_time' => $validated['reset_time'] ?? null,
        'reset_day' => $validated['reset_day'] ?? null,
        'reset_date' => $validated['reset_date'] ?? null,
        'updated_at' => now(),
    ];

    if ($settings) {
        Log::info('Updating existing settings...');
        DB::table('reset_settings')->where('id', $settings->id)->update($data);
    } else {
        Log::info('No settings found, inserting new entry...');
        $data['created_at'] = now();
        DB::table('reset_settings')->insert($data);
    }

    return response()->json([
        'message' => 'Reset settings saved successfully!',
        'data' => $data,
    ]);
}

}
