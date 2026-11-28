<?php

namespace LibreNMS\OS;

use Illuminate\Support\Collection;
use LibreNMS\OS;
use LibreNMS\OS\Traits\NetSnmpSensors;

class Truenas extends OS
{
    use NetSnmpSensors;

    public function discoverStorage(): Collection
    {
        // discover both yaml and HR storage
        return $this->discoverYamlStorage()->merge($this->discoverHrStorage());
    }
}
