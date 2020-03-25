<?php

namespace App\Events;

use App\Models\Device;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CreatingDevice
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $device;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Device $device)
    {
        $this->device = $device;
    }
}
