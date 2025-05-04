<?php

namespace App\Observers;

use App\Models\Eventlog;
use App\Models\Ipv6Nd;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\Severity;
use LibreNMS\Util\IPv6;

class Ipv6NdObserver
{
    public function updated(Ipv6Nd $neighbor): void
    {
        // log mac changes
        if ($neighbor->isDirty('mac_address')) {
            $ipv6 = IPv6::parse($neighbor->ipv6_address)->compressed();
            $old_mac = $neighbor->getOriginal('mac_address');

            Log::debug("Changed mac address for $ipv6 from $old_mac to $neighbor->mac_address");
            Eventlog::log("MAC change: $ipv6 : $old_mac -> $neighbor->mac_address", $neighbor->device_id, 'interface', Severity::Warning, $neighbor->port_id);
        }
    }
}
