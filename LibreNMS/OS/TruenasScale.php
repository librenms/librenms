<?php

namespace LibreNMS\OS;

use Illuminate\Support\Collection;
use LibreNMS\OS;
use LibreNMS\OS\Traits\NetSnmpSensors;

class TruenasScale extends OS
{
    use NetSnmpSensors;

    public function discoverStorage(): Collection
    {
        // discover both yaml and HR storage
        return $this->discoverYamlStorage()->merge($this->discoverHrStorage());
    }

    /**
     * LM-SENSORS-MIB and ups-nut temperatures of the Linux base
     */
    protected function discoverOsSensors(): Collection
    {
        return parent::discoverOsSensors()->merge($this->discoverNetSnmpSensors()->whereIn('sensor_class', ['temperature']));
    }
}
