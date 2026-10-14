<?php

namespace App\Events;

use App\Models\Device;
use Illuminate\Foundation\Events\Dispatchable;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Polling\Method\Config\SnmpConfig;

class SnmpQueryExecuted
{
    use Dispatchable;

    /**
     * @param  string  $target  The target host or IP address queried
     * @param  string  $method  Query method (snmpget, snmpwalk, snmpgetnext, etc.)
     * @param  array<int, string>  $oids  List of OIDs queried
     * @param  float  $duration  Query execution duration in seconds
     * @param  SnmpResponse  $response  The SNMP response object
     * @param  SnmpQueryOptions  $options  The options used for this query
     * @param  SnmpConfig  $config  The SNMP connection settings
     * @param  string  $backend  The name of the SNMP backend used
     * @param  Device|null  $device  The device model, if available
     */
    public function __construct(
        public readonly string $target,
        public readonly string $method,
        public readonly array $oids,
        public readonly float $duration,
        public readonly SnmpResponse $response,
        public readonly SnmpQueryOptions $options,
        public readonly SnmpConfig $config,
        public readonly string $backend,
        public readonly ?Device $device = null,
    ) {
    }
}
