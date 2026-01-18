<?php

namespace LibreNMS\Interfaces\Discovery;

use Illuminate\Support\Collection;

interface FdbTableDiscovery
{
    /**
     * @return Collection<\App\Models\PortsFdb>
     */
    public function discoverFdbTable(): Collection;
}
