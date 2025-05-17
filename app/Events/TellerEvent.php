<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TellerEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $teller;

    public function __construct($teller)
    {
        // assign the incoming student data to the class property
        $this->teller = $teller;
    }

    public function broadcastOn(): Channel
    {
        // this event will be sent on a public channel
        return new Channel('teller-channel');
    }

    // specify a custom event name for broadcasting
    public function broadcastAs(): String {
        // This will be the event name received on the frontend
        return 'TellerEvent';
    }
}

