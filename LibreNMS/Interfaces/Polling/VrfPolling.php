<?php

namespace LibreNMS\Interfaces\Polling;

use App\Models\Vrf;
use Illuminate\Support\Collection;
use LibreNMS\Exceptions\SnmpException;

interface VrfPolling
{
    /**
     * Poll the discovered VRFs. Without this, polling runs discovery again.
     *
     * @param  Collection<int, Vrf>  $vrfs
     * @return Collection<int, Vrf>
     *
     * @throws SnmpException when the device did not answer, existing VRFs are kept
     */
    public function pollVrfs(Collection $vrfs): Collection;
}
