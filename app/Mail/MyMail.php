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
                    background: #1c5d99;
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
    /*                 border-left: 1px solid #393C3F;
                    border-right: 1px solid #393C3F;
                    border-bottom: 1px solid #393C3F; */
                }

                /* Footer section */
                .footer {
                    margin-top: 20px;
                    padding: 10px;
                    text-align: center;
                    font-size: 14px;
                    color: #666666;
                }

                /* Optional button styling */
                .button {
                    background: #1f7ed6;
                    color: #ffffff;
                    padding: 10px 20px;
                    text-decoration: none;
                    font-size: 16px;
                    border-radius: 5px;
                    margin-top: 12px;
                    width: 180px;
                    text-align: center;
                    display: inline-block;
                    box-sizing: border-box;
                }

                /* Button hover effect */
                .button:hover {
                    background: #007bff;
                    color: #ffffff;
                    transform: scale(1.05);
                    transition: all 0.3s ease;
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
                    
                    <table role='presentation' width='100%' cellspacing='0' cellpadding='0' border='0'>
                        <tr>
                            <td align='center'>
                                <a href='http://192.168.0.164:8080/customer-dashboard/{$this->data['token']}' 
                                    class='button' 
                                    style='display: inline-block; background: #007bff; color: #ffffff; 
                                            padding: 10px 20px; text-decoration: none; font-size: 16px; 
                                            border-radius: 5px; margin-top: 10px;'>
                                    Open dashboard
                                </a>
                            </td>
                        </tr>
                    </table>
                    

            </div>
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
