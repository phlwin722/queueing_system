<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminInfoEvent implements ShouldBroadcast
{
    // Use Laravel traits for event functionality
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // Public property that holds the admin or manager data to be broadcast
    public $adminInfo;

    // Constructor method: receives admin/manager data and stores it in the public property
    public function __construct(array $adminInfo)
    {
        $this->adminInfo = $adminInfo;
    }

    // Define the broadcasting channel this event will be sent on
    public function broadcastOn(): Channel
    {
        // Send this event over a public channel named 'admin-info-event-channel'
        return new Channel('admin-info-event-channel');
    }

    // Optionally define a custom name for the broadcasted event
    public function broadcastAs(): string
    {
        // This will be the name of the event on the frontend (e.g., 'AdminInfoEvent')
        return 'AdminInfoEvent';
    }
}
