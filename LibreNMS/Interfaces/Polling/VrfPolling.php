<?php

namespace LibreNMS\Interfaces\Polling;

use App\Models\Vrf;
use Illuminate\Support\Collection;

interface VrfPolling
{
    /**
     * Poll the discovered VRFs. Without this, polling runs discovery again.
     *
     * @param  Collection<int, Vrf>  $vrfs
     * @return Collection<int, Vrf>
     */
    public function pollVrfs(Collection $vrfs): Collection;
}
