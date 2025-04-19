<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function store(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|string|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'service' => 'required',
            'branch_id' => 'required',
            'appointment_date' => 'required|date',
        ]);

        if (!$validate) {
            return response()->json([
                'error' => $validate
            ],422);
        }

        $referenceNumber = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', 10)), 0, 10);


        DB::table('appointments')
                    ->insert([
                        'referenceNumber' => $referenceNumber,
                        'branch_id' => $request->branch_id,
                        'name' => $request->name,
                        'email' => $request->email,
                        'type_id' => $request->service,
                        'appointment_date' => $request->appointment_date
                    ]);

        // slots on available slots

        // Find the slot for the given branch and date
        $slot = DB::table('available_slots')
            ->where('branch_id', $request->branch_id)
            ->where('date', $request->appointment_date)
            ->first();

        if ($slot && $slot->is_available > 0) {
            // Decrement available slots
            DB::table('available_slots')
                ->where('branch_id', $request->branch_id)
                ->where('date', $request->appointment_date)
                ->update(['is_available' => $slot->is_available - 1]);

            return response()->json(['message' => 'Slot successfully booked']);
        } else {
            return response()->json(['message' => 'No available slots left, please select available slot'], 400);
        }

        return response()->json([
            'message' => 'Appointment Booked Successfully!'
        ]);
    }

    public function getSlots(Request $request)
    {
        $datenow = Carbon::now(); // get the current date and time
        $previous_date = $datenow->subDay()->toDateString(); ; // substract 1 day

            // check existing
            DB::table('available_slots')
                            ->where('date', $previous_date)
                            ->delete();
          // Fetch all available slots for the current week (Monday to Friday)
          $availableSlots = DB::table('available_slots')
                                        ->where('branch_id', $request->branch_id)
                                        ->where('is_available', '>', 0)
                                        ->get();
  
          return response()->json(['availableSlots' => $availableSlots]);

    }

    public function services (Request $request) {
        try {   
            $service = DB::table('types')
                        ->where('branch_id',$request->branch_id)
                        ->get();
            return response()->json([
                'servce' => $service
            ]);
        }  catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function apply_slots (Request $request) {
        try {
              // Validate the incoming data
        $validated = $request->validate([
            'branch_id' => 'required|exists:branchs,id',
            'slots_per_day' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $branchId = $validated['branch_id'];
        $slotsPerDay = $validated['slots_per_day'];

        // Loop through each day from start to end date
        $currentDate = $startDate;

        while ($currentDate->lte($endDate)) {
            // Check if the current day is Monday to Friday
            if ($currentDate->isWeekday()) {
                // Check if a slot already exists for the current date
                $existingSlot = DB::table('available_slots')
                    ->where('branch_id', $branchId)
                    ->where('date', $currentDate->toDateString())
                    ->first();

                if ($existingSlot) {
                    // Update the available slots for this day
                    DB::table('available_slots')
                        ->where('branch_id', $branchId)
                        ->where('date', $currentDate->toDateString())
                        ->update(['is_available' => $slotsPerDay]);
                } else {
                    // Insert a new slot record for this day
                    DB::table('available_slots')
                        ->insert([
                            'branch_id' => $branchId,
                            'date' => $currentDate->toDateString(),
                            'is_available' => $slotsPerDay,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                }
            }

            // Move to the next day
            $currentDate->addDay();
        }

        return response()->json(['message' => 'Slots applied successfully for the year.']);
        } catch (\Exception $e) {
            return response()->json ([
                'error' => $e->getMessage()
            ]);
        }
    }
}
