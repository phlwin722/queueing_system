<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use function PHPUnit\Framework\isEmpty;

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
            ], 422);
        }

        // Find the slot for the given branch and date
        $slot = DB::table('available_slots')
            ->where('branch_id', $request->branch_id)
            ->where('date', $request->appointment_date)
            ->first();

        if ($slot && $slot->is_available > 0) {
            $referenceNumber = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', 10)), 0, 10);
             // slots on available slots

            $id = DB::table('appointments')
            ->insertGetId([
                'referenceNumber' => $referenceNumber,
                'branch_id' => $request->branch_id,
                'name' => ucwords($request->name),
                'email' => $request->email,
                'type_id' => $request->service,
                'appointment_date' => $request->appointment_date,
                'created_at' => now(),
                'updated_at' => now(),
            ]);


            // Decrement available slots
            DB::table('available_slots')
                ->where('branch_id', $request->branch_id)
                ->where('date', $request->appointment_date)
                ->update(['is_available' => $slot->is_available - 1]);

            return response()->json([
                'message' => 'Slot successfully booked',
                'id' => $id,
                'referenceNumber' => $referenceNumber,
            ]);
        } else {
            return response()->json(['error' => 'No available slots left, please select available slot'], 400);
        }

        return response()->json([
            'message' => 'Appointment Booked Successfully!'
        ]);
    }

    public function getSlots(Request $request)
    {
        $datenow = Carbon::now(); // get the current date and time
        $previous_date = $datenow->subDay()->toDateString();
        ; // substract 1 day

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

    public function services(Request $request)
    {
        try {
            $service = DB::table('types')
                ->where('branch_id', $request->branch_id)
                ->get();
            return response()->json([
                'servce' => $service
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function apply_slots(Request $request)
    {
        // Validate the incoming data
        $validate = $request->validate([
            'branch_id' => 'required|exists:branchs,id',
            'slots_per_day' => 'required|min:1',
            'start_date' => 'required',
            'time' => 'required',
            'end_date' => 'required',
        ], [
            'branch_id.required' => 'The branch ID is required.',
            'branch_id.exists' => 'The branch ID must exist in the branch table.',
            'slots_per_day.required' => 'The number of slots per day is required.',
            'slots_per_day.min' => 'The number of slots per day must be at least 1.',
            'start_date.required' => 'The start date is required.',
            'time.required' => 'The time field is required.',
            'end_date.required' => 'The end date is required.',
        ]);

        // If validation passes, you can continue processing the request
        // Example: return response()->json(['success' => true]);


        if (!$validate) {
            return response()->json([
                'errors' => $validate
            ], 422);
        }

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();
        $branchId = $request->branch_id;
        $slotsPerDay = $request->slots_per_day;
        $time = $request->time;

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
                        ->update([
                            'is_available' => $slotsPerDay,
                            'prepared_time' => $time,
                        ]);
                } else {
                    // Insert a new slot record for this day
                    DB::table('available_slots')
                        ->insert([
                            'branch_id' => $branchId,
                            'date' => $currentDate->toDateString(),
                            'prepared_time' => $time,
                            'is_available' => $slotsPerDay,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                }
            }

            // Move to the next day
            $currentDate->addDay();
        }

        return response()->json(['message' => 'Slots applied successfully.']);
    }

    public function validateAppointment(Request $request)
    {
        $validate = $request->validate([
            'referenceNum' => 'required'
        ]);

        if (!$validate) {
            return response()->json(['error' => $validate], 422);
        }

        $checkingReference = DB::table('appointments')
            ->where('referenceNumber', $request->referenceNum)
            ->first();
        if ($checkingReference) {
            return response()->json([
                'reference' => $checkingReference
            ]);
        } else {
            return response()->json([
                'error' => 'The reference number could not be found. Please check your email and try again.'
            ], 400);
        }
            $checkingReference = DB::table('appointments')
                                ->where('referenceNumber', $request->referenceNum)
                                ->first();
            if ($checkingReference) {
                if ($checkingReference->status == 'Booked') {
                    $dateNow = Carbon::now()->toDateString();
                    if ($checkingReference->appointment_date === $dateNow) {
                        return response()->json([
                            'reference' => $checkingReference
                        ]);
                    }else {
                        return response()->json([
                            'error' => "We regret to inform you that the appointment set for {$checkingReference->appointment_date} is no longer valid."
                        ],400);
                    }
                }else {
                    return response()->json([
                        'error' => 'The reference number has already been completed.'
                    ],400);
                }
            }else {
                return response()->json([
                    'message' => 'Invalid data format.' // Send error if format is wrong
                ], 422);
            }
    }
    
    public function cancelAppointment(Request $request)
    {
        try {
            // ✅ Step 1: Get the incoming appointment data from the request
            $appointments = $request->dataHandleCancel;
    
            // ✅ Step 2: Validate that the data is present and is an array
            if (!$appointments || !is_array($appointments)) {
                return response()->json([
                    'message' => 'Invalid data format.' // Send error if format is wrong
                ], 422);
            }
    
            // ✅ Step 3: Extract the appointment IDs, branch IDs, and dates into separate arrays
            $ids = array_column($appointments, 'id');                      // [24, 25, ...]
            $branch_ids = array_column($appointments, 'branch_id');        // [1, 2, ...]
            $appointment_dates = array_column($appointments, 'appointment_date'); // ['2025-04-21', ..

            // (Optional) Debug log for inspecting date data
            // Log::info($appointment_dates);

            // ✅ Step 4: Check if at least one slot exists for any of the given dates
            $slots = DB::table('available_slots')
                ->whereIn('date', $appointment_dates)
                ->first();

            // ✅ Step 5: If at least one slot is found, proceed with updates
            if ($slots) {
                // ✅ Step 6: Loop through each appointment and update availability
                foreach ($appointments as $appointment) {
                    DB::table('available_slots')
                        ->where('branch_id', $appointment['branch_id'])             // Match branch
                        ->where('date', $appointment['appointment_date'])          // Match date
                        ->increment('is_available', 1);                            // Add 1 slot back
                }

                // ✅ Step 7: Delete all the cancelled appointments (based on IDs and branches)
                DB::table('appointments')
                    ->whereIn('id', $ids)
                    ->whereIn('branch_id', $branch_ids)
                    ->delete();

                // ✅ Step 8: Return success response
                return response()->json([
                    'message' => 'The appointment has been successfully canceled.'
                ]);
            } else {
                // ❌ If no slots were found, return 404 response
                return response()->json([
                    'message' => 'No slots found for the selected dates.'
                ], 404);
            }

        } catch (\Exception $e) {
            // ❌ Catch any unexpected error and return a 500 server error response
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
  
    public function updateAppointment(Request $request) {
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
            ], 422);
        }

        // get the previous value on appointment  date
        $prevValue = DB::table('appointments')
            ->where('id', $request->id)
            ->first();

        if ($prevValue && $prevValue->appointment_date != $request->appointment_date) {
            //update if not equal previous appointment and new appointment

            // decrease the previous date
            $decreate = DB::table('available_slots')
                ->where('date', $prevValue->appointment_date)
                ->first();

            DB::table('available_slots')
                ->where('date', $prevValue->appointment_date)
                ->update(['is_available' => $decreate->is_available - 1]);

            // increase the latest date
            $increase = DB::table('available_slots')
                ->where('date', $request->appointment_date)
                ->first();

            DB::table('available_slots')
                ->where('date', $request->appointment_date)
                ->update(['is_available' => $increase->is_available + 1]);
        }

        // update appointment
        DB::table('appointments')
            ->where('id', $request->id)
            ->update([
                'branch_id' => $request->branch_id,
                'name' => ucwords($request->name),
                'email' => $request->email,
                'type_id' => $request->type_id,
                'appointment_date' => $request->appointment_date
            ]);

        return response()->json([
            'message' => 'Appointment Booked Update Successfully!   '
        ]);
    }

    public function index (Request $request) {
        try {
            $data = '';
            if ($request->branch_id > 0) {
                $data = $this->getData($request->branch_id, $request->appointment_date);
                return response()->json([
                    'rows' => $data
                ]);
            } else {
                $data = $this->getData(null, $request->appointment_date);
                return response()->json([
                    'rows' => $data
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getData ($branch_id = null, $appointment = null) {
        try {
            $current_date = Carbon::now()->format('Y-m-d');
            // Update all appointments that have a date before today (in the past)
            DB::table('appointments')
                ->where('appointment_date', '<', $current_date)
                ->where('status', '!=', 'Completed')
                ->where('status', '!=', 'Arrived')
                ->update(['status' => 'Expired']);
            
            $res = DB::table('appointments as appt')
                    ->select(
                        'appt.id',
                        'appt.name',
                        'appt.referenceNumber',
                        'appt.branch_id',
                        'appt.email',
                        'appt.type_id',
                        'appt.appointment_date',
                        'appt.status',
                        'b.branch_name',
                        'tp.name as type_name'
                    )
                    
                    ->leftJoin('branchs as b' , 'b.id', '=', 'appt.branch_id')
                    ->leftJoin('types as tp', 'tp.id', '=', 'appt.type_id')
                    ->orderBy('appt.updated_at', 'desc');
 
            if (!empty($appointment)) {
                $res = $res->where('appt.appointment_date', $appointment);
            }
        
            if ($branch_id != null) {
                $res = $res->where('appt.branch_id',$branch_id)->get();
            }else {    
                $res = $res->get();
            }
            return $res;
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function updataStatusAppointment(Request $request)
    {
        try {
            $updateStat = DB::table('appointments')
                ->where('id', $request->id)
                ->update(['status' => $request->status]);

            if ($updateStat) {
                return response()->json([
                    'message' => 'The appointment status has been successfully updated'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }
}