<?php

namespace App\Exceptions;

use App\Models\Device;

class PollingFailedException extends \Exception
{
    public function __construct(Device $device)
    {
        $message = "Failed to poll device $device->device_id: $device->status_reason down";

        parent::__construct($message);
    }
}
