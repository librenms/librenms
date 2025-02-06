<?php

namespace LibreNMS\OS;

use Illuminate\Support\Collection;
use LibreNMS\OS;

class Datadomain extends OS
{
    public function discoverStorage(): Collection
    {
        // this OS uses both yaml and HOST-RESOURCES-MIB
        return $this->discoverYamlStorage()
            ->merge($this->discoverHrStorage());
    }
}
