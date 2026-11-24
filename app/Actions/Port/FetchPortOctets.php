<?php

namespace App\Actions\Port;

use App\Models\Port;
use SnmpQuery;

/**
 * Read a port's current octet counters from the device, preferring the 64-bit counters.
 */
class FetchPortOctets
{
    /**
     * @return array{string, string} in and out octets, empty strings when the device did not answer
     */
    public function execute(Port $port): array
    {
        $ifIndex = $port->ifIndex;

        $response = SnmpQuery::device($port->device)->get([
            "IF-MIB::ifHCInOctets.$ifIndex",
            "IF-MIB::ifInOctets.$ifIndex",
            "IF-MIB::ifHCOutOctets.$ifIndex",
            "IF-MIB::ifOutOctets.$ifIndex",
        ]);

        if (! $response->isValid(true)) {
            return ['', ''];
        }

        $in = $response->value("IF-MIB::ifHCInOctets.$ifIndex");
        if ($in === '') {
            $in = $response->value("IF-MIB::ifInOctets.$ifIndex");
        }

        $out = $response->value("IF-MIB::ifHCOutOctets.$ifIndex");
        if ($out === '') {
            $out = $response->value("IF-MIB::ifOutOctets.$ifIndex");
        }

        return [$in, $out];
    }
}
