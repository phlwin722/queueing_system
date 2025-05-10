<?php
namespace App\Http\Controllers;

use App\Events\StudentCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function insert(Request $request)
    {
 
        // Insert the student into the database using raw SQL
        $student = DB::table('students')->insertGetId([
            'name' => $request->name,
            'grade' => $request->grade,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Fetch the newly inserted student from the database
        $newStudent = DB::table('students')->where('id', $student)->first();

        // Broadcast the event to the students channel
        broadcast(new StudentCreated($newStudent));

        // Return the newly created student as a response
        return response()->json(['message' => 'Student created successfully', 'student' => $newStudent]);
    }
}
