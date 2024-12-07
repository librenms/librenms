<?php

namespace App\Events;

use App\Models\Device;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PollingDevice
{
    use Dispatchable, SerializesModels;

    /**
     * @var \App\Models\Device
     */
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
