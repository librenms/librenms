<?php

namespace LibreNMS\Tests\Unit\Listeners;

use App\Events\SnmpQueryExecuted;
use App\Listeners\PrintSnmpDebugOutput;
use Illuminate\Support\Facades\Log;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Polling\Method\Config\SnmpConfig;
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
            target: '127.0.0.1',
            method: 'snmpget',
            oids: ['sysDescr.0', 'sysObjectID.0'],
            duration: 0.42,
            response: $response,
            options: new SnmpQueryOptions,
            config: new SnmpConfig,
            backend: 'NetSnmp',
        );

        Log::shouldReceive('debug')
            ->once()
            ->with('SNMP[%csnmpget sysDescr.0 sysObjectID.0%n] 420ms', ['color' => true]);

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

    public function testPrintsDurationWhenProvided(): void
    {
        Debug::set(true);
        Debug::setVerbose(false);

        $response = new SnmpResponse([
            'sysDescr.0' => 'Linux 6.1',
        ]);

        $event = new SnmpQueryExecuted(
            target: '127.0.0.1',
            method: 'snmpget',
            oids: ['sysDescr.0'],
            duration: 0.01524,
            response: $response,
            options: new SnmpQueryOptions,
            config: new SnmpConfig,
            backend: 'NetSnmp',
        );

        Log::shouldReceive('debug')
            ->once()
            ->with('SNMP[%csnmpget sysDescr.0%n] 15.24ms', ['color' => true]);

        Log::shouldReceive('debug')
            ->once()
            ->with("sysDescr.0 = Linux 6.1\n");

        try {
            (new PrintSnmpDebugOutput())->handle($event);
        } finally {
            Debug::set(false);
            Debug::setVerbose(false);
        }
    }
}
