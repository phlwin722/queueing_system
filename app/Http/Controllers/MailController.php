<?php

namespace App\Http\Controllers;

use App\Mail\ThankyouMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Mail\MyMail; // Importing the mail class
use App\Mail\SendingDashBoard;
use Illuminate\Support\Facades\Mail; // Importing Laravel's mail functionality

class MailController extends Controller
{
    /**
     * Handles the email sending request.
     */

     public function sentEmailDashboard (Request $request) {
        try {
            // Extracting user input from the request
            $data = [
                'id' => $request->id,
                'token' => $request->token,
                'name' => $request->name,
                'email' => $request->email,
                'subject' => $request->subject,
                'message' => $request->message,
                'queue_number' => $request->queue_number,
            ];

                // update email status of customer sended 
                $update = DB::table('queues')
                ->where('id', $data['id'])
                ->update([
                    'email_status' => 'pending_alert',
                    'updated_at' => now () // Optional: update timestamp
                ]);

            if ($update) {
                // Returning a JSON response to confirm the email was sent successfully
                Mail::to($data['email'])->send(new SendingDashBoard($data));

                return response()->json([
                    'message' => 'Email sent successfully',
                ]);
            }
        }catch (\Exception $e) {
            $message = $e->getMessage();
            return response()->json([
                'message' => $message
            ]);
        }
     }
    public function sendEmail(Request $request)
    {
        // Extracting user input from the request
        $data = [
            'id' => $request->id,
            'token' => $request->token,
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
        ];

        // update email status of customer sended 
        $update = DB::table('queues')
         ->where('id', $data['id'])
         ->update([
            'email_status' => 'sending_alert',
            'updated_at' => now () // Optional: update timestamp
         ]);

        if ($update) {
            // Sending the email to the recipient using Laravel's Mail Facade
            Mail::to($data['email'])->send(new MyMail($data));
            // Returning a JSON response to confirm the email was sent successfully
            return response()->json([
                'message' => 'Email sent successfully',
            ]);
        }
    }

    public function sendEmailFinish (Request $request) {
        // Extracting user input from the request
        $data = [
            'id' => $request->id,
            'email' => $request->email,
            'subject' => $request->subject,
        ];

        $update = DB::table('queues')
        ->where('id', $data['id'])
        ->update([
            'email_status' => 'thankyou_sending',
            'updated_at' => now()
        ]);
        
        if ($update) {
            // Sending the email to the recipient using Laravel's Mail Facade
            Mail::to($data['email'])->send(new ThankyouMail($data));

            // Returning a JSON response to confirm the email was sent successfully
            return response()->json([
                'message' => 'Email sent successfully',
            ]);
        }
    }
}
