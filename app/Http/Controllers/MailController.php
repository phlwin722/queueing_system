<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\MyMail; // Importing the mail class
use Illuminate\Support\Facades\Mail; // Importing Laravel's mail functionality

class MailController extends Controller
{
    /**
     * Handles the email sending request.
     */
    public function sendEmail(Request $request)
    {
        // Extracting user input from the request
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
        ];

        // Sending the email to the recipient using Laravel's Mail Facade
        Mail::to($data['email'])->send(new MyMail($data));

        // Returning a JSON response to confirm the email was sent successfully
        return response()->json(['message' => 'Email sent successfully']);
    }
}
