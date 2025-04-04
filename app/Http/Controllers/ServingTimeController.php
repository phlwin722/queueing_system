<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServingTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ServingTimeController extends Controller
{
    public function saveServingTime(Request $request)
    {
        $request->validate([
            'minutes' => 'required|integer',
            'type_id' => 'nullable|exists:types,id',
            'teller_id' => 'nullable|exists:tellers,id',
        ]);

        DB::table('serving_times')->insert([
            'minutes' => $request->minutes,
            'type_id' => $request->type_id,
            'teller_id' => $request->teller_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Serving time saved successfully']);
    }

    public function getTodayServingStats(Request $request)
    {
        $yesterday = Carbon::yesterday();

        $stats = DB::table('serving_times')
            ->where('type_id', $request->type_id)
            ->whereDate('updated_at', $yesterday)
            ->selectRaw('MIN(minutes) as min_minutes, MAX(minutes) as max_minutes, AVG(minutes) as avg_minutes')
            ->first();

        return response()->json([
            'min' => $stats->min_minutes ? (int) $stats->min_minutes : 0,
            'max' => $stats->max_minutes ? (int) $stats->max_minutes : 0,
            'avg' => $stats->avg_minutes ? round($stats->avg_minutes, 2) : 0,
        ]);
    }

    public function updateServingTime(Request $request){
        $minutes = $request->minutes;
        $type_id = $request->type_id;

        DB::table('types')
        ->where('id', $type_id)
        ->update(['serving_time' => $minutes]);
    }

    public function updateAllServingTime(Request $request)
    {
        // Get all the type IDs
        $type_ids = DB::table('types')->pluck('id');

        // Get yesterday's date
        $yesterday = Carbon::yesterday();

        // Get the average serving time for each type_id for yesterday
        $stats = DB::table('serving_times')
            ->whereIn('type_id', $type_ids) // Filter by the selected type_ids
            ->whereDate('updated_at', $yesterday) // Filter by yesterday's date
            ->selectRaw('type_id, AVG(minutes) as avg_minutes') // Select type_id and average minutes
            ->groupBy('type_id') // Group by type_id to get the average per type
            ->get();

        // Loop through each type and update its serving_time based on the fetched average
        foreach ($type_ids as $type_id) {
            // Check if an average was found for the type_id
            $stat = $stats->firstWhere('type_id', $type_id);

            // If the average exists, update with avg_minutes, otherwise set it to null
            DB::table('types')
                ->where('id', $type_id)
                ->update(['serving_time' => $stat ? $stat->avg_minutes : null]);
        }
        return response()->json(['status' => 'success']);

    }
}
