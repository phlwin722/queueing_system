<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WindowArchive;

class WindowArchiveController extends Controller
{
    public function getArchivedWindows(Request $request)
    {
        $query = WindowArchive::query();

        // Optional: Filter by date
        if ($request->date) {
            $query->whereDate('archived_at', $request->date);
        }

        return response()->json([
            'rows' => $query->orderBy('archived_at', 'desc')->get()
        ]);
    }
}

