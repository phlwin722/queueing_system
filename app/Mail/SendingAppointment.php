<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendingAppointment extends Mailable
{
    use Queueable, SerializesModels;

    public $data; // public property hold email data

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Builds the email message.
     */

    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS'))
            ->subject($this->data['subject'])
            ->html($this->generatedEmailContent());
    }

    private function generatedEmailContent()
    {
        return "
            <html>
            <head>
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
          /*         border-left: 1px solid #393C3F; 
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
                    .queue-center {
                        text-align: center;
                    }
                </style>
            </head>
            <body>
                <div class='email-container'>
                    <!-- Email Header -->
                    <div class='header'>VRTSystems</div>
                    
                    <!-- Email Content -->
                    <div class='content'>
		            	<div class='queue-center'>
                            <h4>Reference Number</h4>
                            <h2>{$this->data['referenceNumber']}</h2>
			            </div>
                        <h4>Hello, {$this->data['name']}</h4>
                        <p>
                          We have successfully scheduled your appointment for the {$this->data['service_name']} service. 
                          The appointment is confirmed for {$this->data['appointment_date']}. 
                          It will take place at the {$this->data['branch_name']} branch. 
                          Please ensure you arrive on time for your scheduled appointment. 
                          Should you need to reschedule or cancel, feel free to contact us at any time.
                        </p>           
                        <p>We appreciate your time!</p>
  
                    
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
