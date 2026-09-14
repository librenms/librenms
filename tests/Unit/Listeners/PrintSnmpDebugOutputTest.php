<?php

namespace LibreNMS\Tests\Unit\Listeners;

use App\Events\SnmpQueryExecuted;
use App\Listeners\PrintSnmpDebugOutput;
use Illuminate\Support\Facades\Log;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Tests\TestCase;
use LibreNMS\Util\Debug;

final class PrintSnmpDebugOutputTest extends TestCase
{
    public function testHandlesArrayResponseUsingRawOutput(): void
    {
        Debug::set(true);
        Debug::setVerbose(true);

        $response = new SnmpResponse([
            'sysDescr.0' => 'Linux 6.1',
            'sysObjectID.0' => '1.3.6.1.4.1.8072.3.2.10',
        ]);

        $event = new SnmpQueryExecuted(
            method: 'snmpget',
            oids: ['sysDescr.0', 'sysObjectID.0'],
            response: $response,
            cliCommand: [],
        );

        Log::shouldReceive('debug')
            ->once()
            ->with('SNMP[%csnmpget sysDescr.0 sysObjectID.0%n]', ['color' => true]);

        Log::shouldReceive('debug')
            ->once()
            ->with("sysDescr.0 = Linux 6.1\nsysObjectID.0 = 1.3.6.1.4.1.8072.3.2.10\n");

        try {
            (new PrintSnmpDebugOutput())->handle($event);
        } finally {
            Debug::set(false);
            Debug::setVerbose(false);
        }
    }
}
