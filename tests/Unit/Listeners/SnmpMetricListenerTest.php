<?php

namespace LibreNMS\Tests\Unit\Listeners;

use App\Events\SnmpQueryExecuted;
use App\Listeners\SnmpMetricListener;
use App\Polling\Measure\MeasurementManager;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Tests\TestCase;

final class SnmpMetricListenerTest extends TestCase
{
    public function testRecordsSnmpMeasurement(): void
    {
        $manager = app(MeasurementManager::class);
        $initialCount = $manager->getCategory('snmp')->getTotalCount();
        $initialDuration = $manager->getCategory('snmp')->getTotalDuration();

        $event = new SnmpQueryExecuted(
            target: '127.0.0.1',
            method: 'snmpget',
            oids: ['sysDescr.0'],
            duration: 0.123,
            response: new SnmpResponse(['sysDescr.0' => 'Linux']),
            options: new SnmpQueryOptions,
            config: new \LibreNMS\Polling\Method\Config\SnmpConfig,
            backend: 'NetSnmp',
        );

        (new SnmpMetricListener())->handle($event);

        $this->assertSame($initialCount + 1, $manager->getCategory('snmp')->getTotalCount());
        $this->assertEqualsWithDelta($initialDuration + 0.123, $manager->getCategory('snmp')->getTotalDuration(), 0.0001);
    }

    public function testRecordsSnmpBackendMeasurement(): void
    {
        $manager = app(MeasurementManager::class);
        $initialCount = $manager->getCategory('snmp_backend')->getTotalCount();
        $initialDuration = $manager->getCategory('snmp_backend')->getTotalDuration();

        $event = new SnmpQueryExecuted(
            target: '127.0.0.1',
            method: 'snmpget',
            oids: ['sysDescr.0'],
            duration: 0.045,
            response: new SnmpResponse(['sysDescr.0' => 'Linux']),
            options: new SnmpQueryOptions,
            config: new \LibreNMS\Polling\Method\Config\SnmpConfig,
            backend: 'PhpExtension',
        );

        (new SnmpMetricListener())->handle($event);

        $this->assertSame($initialCount + 1, $manager->getCategory('snmp_backend')->getTotalCount());
        $this->assertEqualsWithDelta($initialDuration + 0.045, $manager->getCategory('snmp_backend')->getTotalDuration(), 0.0001);
    }
}
