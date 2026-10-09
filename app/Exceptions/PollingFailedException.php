<?php

namespace App\Exceptions;

use App\Models\Device;
use Illuminate\Contracts\Debug\ShouldntReport;

/**
 * Thrown to make the queue retry polling a down device, a down device is not an application error
 */
class PollingFailedException extends \Exception implements ShouldntReport
{
    public function __construct(Device $device)
    {
        $message = "Failed to poll device $device->device_id: $device->status_reason down";

        parent::__construct($message);
    }
}
