<?php

namespace LibreNMS\Tests\Unit\Http\Formatters;

use App\Http\Formatters\AlertLogDetailFormatter;
use LibreNMS\Tests\TestCase;

class AlertLogDetailFormatterTest extends TestCase
{
    private AlertLogDetailFormatter $formatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formatter = new AlertLogDetailFormatter();
    }

    public function testFormatBasicRuleAlert(): void
    {
        $details = [
            'rule' => [
                ['message' => 'Test alert', 'value' => 10]
            ]
        ];

        $output = $this->formatter->format($details);

        $this->assertStringContainsString('#1:', $output);
        $this->assertStringContainsString('message: Test alert', $output);
        $this->assertStringContainsString('value: 10', $output);
    }

    public function testFormatAlertWithDiff(): void
    {
        $details = [
            'diff' => [
                'added' => [
                    ['message' => 'New item']
                ],
                'resolved' => [
                    ['message' => 'Fixed item']
                ]
            ],
            'rule' => [
                ['message' => 'Still alert']
            ]
        ];

        $output = $this->formatter->format($details);

        $this->assertStringContainsString('<b>Modifications:</b>', $output);
        $this->assertStringContainsString('Added #1: message: New item', $output);
        $this->assertStringContainsString('Resolved #1: message: Fixed item', $output);
        $this->assertStringContainsString('<b>All current items:</b>', $output);
        $this->assertStringContainsString('#1: message: Still alert', $output);
    }

    public function testFormatBill(): void
    {
        $details = [
            'rule' => [
                [
                    'bill_id' => 123,
                    'bill_name' => 'Test Bill'
                ]
            ]
        ];

        $output = $this->formatter->format($details);

        $this->assertStringContainsString('Bill:', $output);
        $this->assertStringContainsString('bill_id=123', $output);
        $this->assertStringContainsString('Test Bill', $output);
    }

    public function testFormatPort(): void
    {
        $details = [
            'rule' => [
                [
                    'port_id' => 1,
                    'device_id' => 1,
                    'ifDescr' => 'eth0',
                    'ifAlias' => 'WAN Interface',
                ]
            ]
        ];

        $output = $this->formatter->format($details);

        $this->assertStringContainsString('Port:', $output);
        $this->assertStringContainsString('eth0', $output);
        $this->assertStringContainsString('Alias: WAN Interface', $output);
    }

    public function testFormatSensor(): void
    {
        $details = [
            'rule' => [
                [
                    'device_id' => 1,
                    'sensor_id' => 456,
                    'sensor_class' => 'temperature',
                    'sensor_current' => 35,
                    'sensor_limit' => 40,
                    'sensor_limit_warn' => 38,
                    'sensor_descr' => 'CPU Temp'
                ]
            ]
        ];

        $output = $this->formatter->format($details);

        $this->assertStringContainsString('Sensor:', $output);
        $this->assertStringContainsString('Value: 35', $output); // Check for value, ignore unit if it varies
        $this->assertStringContainsString('(temperature)', $output);
        $this->assertStringContainsString('Thresholds: High Warn: 38, High: 40', $output);
    }

    public function testFormatSensorState(): void
    {
        $details = [
            'rule' => [
                [
                    'device_id' => 1,
                    'sensor_id' => 789,
                    'sensor_class' => 'state',
                    'sensor_current' => 2,
                    'state_descr' => 'Critical',
                    'state_value' => 2, // Used by StateTranslation if constructor receives it
                    'sensor_descr' => 'Power State'
                ]
            ]
        ];

        $output = $this->formatter->format($details);

        $this->assertStringContainsString('Sensor:', $output);
        // The formatter pre-loads the translation if state_descr is set
        $this->assertStringContainsString('State: Critical (numerical: 2)', $output);
    }

    public function testFormatAccessPoint(): void
    {
        $details = [
            'rule' => [
                [
                    'accesspoint_id' => 101,
                    'device_id' => 1,
                    'name' => 'AP-01'
                ]
            ]
        ];

        $output = $this->formatter->format($details);

        $this->assertStringContainsString('Access Point:', $output);
        $this->assertStringContainsString('accesspoints/ap=101', $output);
        $this->assertStringContainsString('AP-01', $output);
    }

    public function testFormatService(): void
    {
        $details = [
            'rule' => [
                [
                    'service_id' => 202,
                    'device_id' => 1,
                    'service_name' => 'HTTP',
                    'service_type' => 'http',
                    'service_ip' => '1.2.3.4',
                    'service_desc' => 'Web Service',
                    'service_message' => 'Connection refused'
                ]
            ]
        ];

        $output = $this->formatter->format($details);

        $this->assertStringContainsString('Service:', $output);
        $this->assertStringContainsString('services/view=detail', $output);
        $this->assertStringContainsString('HTTP (http)', $output);
        $this->assertStringContainsString('Host: 1.2.3.4', $output);
        $this->assertStringContainsString('Description: Web Service', $output);
        $this->assertStringContainsString('Message: Connection refused', $output);
    }

    public function testFormatBgpPeer(): void
    {
        $details = [
            'rule' => [
                [
                    'bgpPeer_id' => 303,
                    'device_id' => 1,
                    'bgpPeerIdentifier' => '10.0.0.1',
                    'bgpPeerDescr' => 'ISP-A',
                    'bgpPeerRemoteAs' => 65001,
                    'bgpPeerState' => 'idle'
                ]
            ]
        ];

        $output = $this->formatter->format($details);

        $this->assertStringContainsString('BGP Peer:', $output);
        $this->assertStringContainsString('routing/proto=bgp', $output);
        $this->assertStringContainsString('10.0.0.1', $output);
        $this->assertStringContainsString('Description: ISP-A', $output);
        $this->assertStringContainsString('Remote AS: 65001', $output);
        $this->assertStringContainsString('State: idle', $output);
    }

    public function testFormatMempool(): void
    {
        $details = [
            'rule' => [
                [
                    'mempool_id' => 404,
                    'mempool_descr' => 'System RAM',
                    'mempool_perc' => 85.5,
                    'mempool_free' => 1024 * 1024 * 100, // 100MB
                    'mempool_total' => 1024 * 1024 * 1024 // 1GB
                ]
            ]
        ];

        $output = $this->formatter->format($details);

        $this->assertStringContainsString('Memory Pool:', $output);
        $this->assertStringContainsString('mempool_usage/404', $output);
        $this->assertStringContainsString('System RAM', $output);
        $this->assertStringContainsString('Usage: 85.5', $output);
        $this->assertStringContainsString('Free: 104.86 MB', $output);
        $this->assertStringContainsString('Total: 1.07 GB', $output);
    }

    public function testFormatApplication(): void
    {
        $details = [
            'rule' => [
                [
                    'app_id' => 505,
                    'device_id' => 1,
                    'app_type' => 'nginx',
                    'app_status' => 'up',
                    'metric' => 'requests',
                    'value' => 5000
                ]
            ]
        ];

        $output = $this->formatter->format($details);

        $this->assertStringContainsString('Application:', $output);
        $this->assertStringContainsString('apps/app=nginx', $output);
        $this->assertStringContainsString('nginx', $output);
        $this->assertStringContainsString('Status: up', $output);
        $this->assertStringContainsString('Metric: requests = 5000', $output);
    }

    public function testFallbackFormatting(): void
    {
        $details = [
            'rule' => [
                [
                    'custom_key' => 'custom_value',
                    'device_id' => 1, // should be skipped
                    'some_id' => 123, // should be skipped (contains id)
                    'description' => 'test', // should be skipped (contains desc)
                    'another_val' => 'present'
                ]
            ]
        ];

        $output = $this->formatter->format($details);

        $this->assertStringContainsString('custom_key: custom_value', $output);
        $this->assertStringContainsString('another_val: present', $output);
        $this->assertStringNotContainsString('device_id:', $output);
        $this->assertStringNotContainsString('some_id:', $output);
        $this->assertStringNotContainsString('description:', $output);
    }
}
