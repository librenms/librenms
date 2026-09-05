<?php

namespace LibreNMS\Interfaces\Discovery;

use Illuminate\Support\Collection;

interface LinkDiscovery
{
    /**
     * @return Collection<int, \App\Models\Link>
     */
    public function discoverLinks(): Collection;
}
