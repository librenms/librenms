<?php

namespace App\Events;

use App\Models\Device;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiscoveringDevice
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Device $device
    ) {
    }
}
