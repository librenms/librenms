<?php

namespace App\Observers;

use App\Models\Eventlog;
use App\Models\Ipv4Mac;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\Severity;
use LibreNMS\Util\Mac;

class Ipv4MacObserver
{
    public function updated(Ipv4Mac $arp): void
    {
        // log mac changes
        if ($arp->isDirty('mac_address')) {
            $old_mac = $arp->getOriginal('mac_address');
            Log::debug("Changed mac address for $arp->ipv4_address from $old_mac to $arp->mac_address");
            Eventlog::log("MAC change: $arp->ipv4_address : " . Mac::parse($old_mac)->readable() . ' -> ' . Mac::parse($arp->mac_address)->readable(), $arp->device_id, 'interface', Severity::Warning, $arp->port_id);
        }
    }
}
