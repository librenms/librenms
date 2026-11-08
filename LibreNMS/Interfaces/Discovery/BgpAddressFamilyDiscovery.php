<?php

namespace LibreNMS\Interfaces\Discovery;

use App\Models\BgpPeer;
use App\Models\BgpPeerCbgp;
use Illuminate\Support\Collection;
use LibreNMS\Exceptions\SnmpException;

interface BgpAddressFamilyDiscovery
{
    /**
     * Discover the prefix counters of each address family of the sessions, polling runs this again.
     * bgpPeerIdentifier and context_name must match the session they belong to.
     *
     * @param  Collection<int, BgpPeer>  $peers  discovered sessions
     * @return Collection<int, BgpPeerCbgp>
     *
     * @throws SnmpException when the device did not answer, existing address families are kept
     */
    public function discoverBgpAddressFamilies(Collection $peers): Collection;
}
