<?php

namespace LibreNMS\Interfaces\Discovery;

use Illuminate\Support\Collection;

interface LinkDiscovery
{
    /**
     * @return Collection<\App\Models\Link>
     */
    public function discoverLinks(): Collection;
}
