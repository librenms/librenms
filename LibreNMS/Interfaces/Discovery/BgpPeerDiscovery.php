<?php

namespace LibreNMS\Interfaces\Discovery;

use App\Models\BgpPeer;
use Illuminate\Support\Collection;
use LibreNMS\Exceptions\SnmpException;

interface BgpPeerDiscovery
{
    /**
     * Discover the BGP sessions of the device in every VRF, polling runs this again.
     * Read tables without a VRF index in each of $this->getDevice()->getVrfContexts().
     * Set context_name to the context the session was read from and vrf_id to its VRF.
     * Call throwIfConnectionError() on the queries that list the sessions, so a device that did not
     * answer is not mistaken for a device without sessions.
     *
     * @return Collection<int, BgpPeer>
     *
     * @throws SnmpException when the device did not answer, existing sessions are kept
     */
    public function discoverBgpPeers(): Collection;

    /**
     * The local AS of the device, null if it is not known
     */
    public function discoverBgpLocalAs(): ?int;
}
