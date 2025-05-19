<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentCreated implements ShouldBroadcast
{
    // Use Laravel traits to enable dispatching, sockets, and safe serialization
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // Public property to store the student data that will be broadcast
    public $student;

    // Constructor: accepts student data when the event is created
    public function __construct($student)
    {
        // Assign the incoming student data to the class property
        $this->student = $student;
    }

    // Define the channel on which the event should be broadcast
    public function broadcastOn(): Channel
    {
        // This event will be sent on a public channel called 'students-channel'
        return new Channel('students-channel');
    }

    // Optionally specify a custom event name for broadcasting
    public function broadcastAs(): string
    {
        // This will be the event name received on the frontend (e.g., 'StudentCreated')
        return 'StudentCreated';
    }
}
