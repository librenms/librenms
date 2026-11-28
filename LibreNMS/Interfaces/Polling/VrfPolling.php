<?php

namespace LibreNMS\Interfaces\Polling;

use App\Models\Vrf;
use Illuminate\Support\Collection;
use LibreNMS\Exceptions\SnmpConnectionException;

interface VrfPolling
{
    /**
     * Poll the discovered VRFs. Without this, polling runs discovery again.
     *
     * @param  Collection<int, Vrf>  $vrfs
     * @return Collection<int, Vrf>
     *
     * @throws SnmpConnectionException when the device did not answer, existing VRFs are kept
     */
    public function pollVrfs(Collection $vrfs): Collection;
}
