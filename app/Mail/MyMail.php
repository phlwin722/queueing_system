<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data; // Public property to hold email data

    /**
     * Constructor to initialize email data.
     */
    public function __construct($data)
    {
        $this->data = $data; // Assigning the passed data to the class property
    }

    /**
     * Builds the email message.
     */
    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS')) // Fetch sender email from .env
                    ->subject($this->data['subject']) // Set the email subject dynamically
                    ->html($this->generateEmailContent()); // Generate HTML email content dynamically
    }

    /**
     * Dynamically generates the email content using inline HTML & CSS.
     */
    private function generateEmailContent()
    {
        return "
        <html>
            <head>
                <title>{$this->data['subject']}</title>
                <style>
                    /* General styles for the email */
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f4f4f4;
                        margin: 0;
                        padding: 0;
                    }
                    
                    /* Email container */
                    .email-container {
                        max-width: 600px;
                        margin: 20px auto;
                        background: #ffffff;
                        padding: 20px;
                        border-radius: 8px;
                        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
                    }

                    /* Header styles */
                    .header {
                        background: #007bff;
                        color: #ffffff;
                        padding: 15px;
                        text-align: left;
                        font-size: 22px;
                        font-weight: bold;
                        border-radius: 8px 8px 0 0;
                    }

                    /* Content section */
                    .content {
                        padding: 20px;
                        font-size: 16px;
                        color: #333333;
                        line-height: 1.6;
                    }

                    /* Footer section */
                    .footer {
                        margin-top: 20px;
                        padding: 10px;
                        text-align: center;
                        font-size: 14px;
                        color: #666666;
                        border-top: 1px solid #dddddd;
                    }

                    /* Optional button styling */
                    .button {
                        display: inline-block;
                        background: #007bff;
                        color: #ffffff;
                        padding: 10px 20px;
                        text-decoration: none;
                        font-size: 16px;
                        border-radius: 5px;
                        margin-top: 10px;
                    }

                    /* Button hover effect */
                    .button:hover {
                        background: #0056b3;
                        color: #ffffff;
                    }
                </style>
            </head>
            <body>
                <div class='email-container'>
                    <!-- Email Header -->
                    <div class='header'>VRTSystems</div>
                    
                    <!-- Email Content -->
                    <div class='content'>
                        <h2>Hello, {$this->data['name']}!</h2>
                        <p>{$this->data['message']}</p>
                        <p>We appreciate your time!</p>
                    </div>

                    <a href='http://192.168.0.164:8080/customer-dashboard/{$this->data['token']}' class='button'>Open my dashboard</a>

                    <!-- Email Footer -->
                    <div class='footer'>
                        &copy; " . date('Y') . " VRTSYSTEMS TECHNOLOGIES CORPORATION. All rights reserved.
                    </div>
                </div>
            </body>
        </html>
        ";
    }
}
