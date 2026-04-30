<?php

namespace LibreNMS\Interfaces\Discovery;

use Illuminate\Support\Collection;

interface FdbTableDiscovery
{
    /**
     * @return Collection<int, \App\Models\PortsFdb>
     */
    public function discoverFdbTable(): Collection;
}
