<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WindowArchive;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WindowArchiveController extends Controller
{
    public function getWindowLogs(Request $request)
{
    $query = DB::table('window_archives as wa')
        ->select(
            "wa.id",
            "wa.window_name",
            "tp.name as type_name",
            DB::raw("COALESCE(CONCAT(ts.teller_firstname, ' ', ts.teller_lastname), 'Unassigned') as teller_name"),
            "wa.archived_at"
        )
        ->leftJoin("types as tp", "tp.id", "wa.type_id")
        ->leftJoin("tellers as ts", "ts.id", "wa.teller_id")
        ->orderBy('wa.archived_at', 'desc');

    // Filter by selected date if provided
    if ($request->has('date')) {
        $query->whereDate('wa.archived_at', $request->date);
    }

    $logs = $query->get();

    return response()->json([
        'rows' => $logs
    ]);
}
}

