<?php

namespace App\Actions\Device;

use App\Models\Device;
use LibreNMS\Enum\DeviceAddOutcome;

readonly class AddFoundDeviceResult
{
    public function __construct(
        public DeviceAddOutcome $outcome,
        public string $message = '',
        public ?Device $device = null, // the added device
    ) {
    }

    public function added(): bool
    {
        return $this->outcome === DeviceAddOutcome::Added;
    }
}
