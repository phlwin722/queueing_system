<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;

class AppointmentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'contact' => 'required|string|max:20',
            'service' => 'required|string',
            'branch' => 'required|string',
            'appointment_date' => 'required|date',
        ]);

        Appointment::create($request->all());

        return response()->json(['message' => 'Appointment Booked Successfully!']);
    }

    public function getSlots($branchName)
    {
        $start = now()->startOfMonth()->format('Y-m-d');
        $end = now()->endOfMonth()->format('Y-m-d');

        $appointments = Appointment::where('branch_name', $branchName)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->groupBy('date');

        $slotsPerDay = [];
        foreach ($appointments as $date => $items) {
            $slotsPerDay[$date] = 150 - $items->count(); // Assuming 150 max/day
        }

        return response()->json($slotsPerDay);
    }



}
