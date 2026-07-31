<?php

namespace App\Events;

use App\Models\Device;
use Illuminate\Foundation\Events\Dispatchable;
use LibreNMS\Data\Source\SnmpResponse;

class SnmpQueryExecuted
{
    use Dispatchable;

    /**
     * @param  string  $method  snmpget, snmpwalk, snmpgetnext, snmptranslate, etc.
     * @param  array<int, string>  $oids  List of OIDs queried
     * @param  array<int, string>  $cliCommand  The raw CLI command array
     * @param  SnmpResponse  $response  The SNMP response object
     * @param  Device|null  $device  The device object, if available
     * @param  string  $context  SNMP context, if set
     * @param  array<int, string>  $mibs  List of MIBs included
     * @param  string|null  $mibDir  MIB directories used
     */
    public function __construct(
        public readonly string $method,
        public readonly array $oids,
        public readonly array $cliCommand,
        public readonly SnmpResponse $response,
        public readonly ?Device $device = null,
        public readonly string $context = '',
        public readonly array $mibs = [],
        public readonly ?string $mibDir = null,
    ) {
    }
}
