<?php

namespace App\Actions\Device;

use LibreNMS\Enum\DeviceAddOutcome;

readonly class AddFoundDeviceResult
{
    public function __construct(
        public DeviceAddOutcome $outcome,
        public string $message = '',
    ) {
    }

    public function added(): bool
    {
        return $this->outcome === DeviceAddOutcome::Added;
    }
}
