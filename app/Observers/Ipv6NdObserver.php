<?php

namespace App\Observers;

use App\Models\Eventlog;
use App\Models\Ipv6Nd;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\Severity;
use LibreNMS\Util\IPv6;
use LibreNMS\Util\Mac;

class Ipv6NdObserver
{
    public function updated(Ipv6Nd $neighbor): void
    {
        // log mac changes
        if ($neighbor->isDirty('mac_address')) {
            $ipv6 = IPv6::parse($neighbor->ipv6_address)->compressed();
            $old_mac = Mac::parse($neighbor->getOriginal('mac_address'))->readable();
            $new_mac = Mac::parse($neighbor->mac_address)->readable();

            if ($old_mac !== $new_mac) { // do not log formatting change
                Log::debug("Changed mac address for $ipv6 from $old_mac to $new_mac");
                Eventlog::log("MAC change: $ipv6 : $old_mac -> $new_mac", $neighbor->device_id, 'interface', Severity::Warning, $neighbor->port_id);
            }
        }
    }
}
