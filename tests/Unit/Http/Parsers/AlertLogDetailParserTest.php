<?php

namespace LibreNMS\Tests\Unit\Http\Parsers;

use App\Http\Parsers\AlertLogDetailParser;
use LibreNMS\Tests\TestCase;

class AlertLogDetailParserTest extends TestCase
{
    private AlertLogDetailParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new AlertLogDetailParser();
    }

    public function testFormatBasicRuleAlert(): void
    {
        $details = [
            'rule' => [
                ['message' => 'Test alert', 'value' => 10],
            ],
        ];

        $output = $this->parser->parse($details);

        $this->assertEquals(0, $output['sections'][0]['items'][0]['row']);
        $this->assertEquals('message', $output['sections'][0]['items'][0]['fields'][0]['label']);
        $this->assertEquals('Test alert', $output['sections'][0]['items'][0]['fields'][0]['value']);
        $this->assertEquals('value', $output['sections'][0]['items'][0]['fields'][1]['label']);
        $this->assertEquals('10', $output['sections'][0]['items'][0]['fields'][1]['value']);
    }

    public function testFormatAlertWithDiff(): void
    {
        $details = [
            'diff' => [
                'added' => [
                    ['message' => 'New item'],
                ],
                'resolved' => [
                    ['message' => 'Fixed item'],
                ],
            ],
            'rule' => [
                ['message' => 'Still alert'],
            ],
        ];

        $output = $this->parser->parse($details);

        $this->assertEquals('Modifications', $output['sections'][0]['title']);
        $this->assertEquals('added', $output['sections'][0]['items'][0]['type']);
        $this->assertEquals('New item', $output['sections'][0]['items'][0]['fields'][0]['value']);
        $this->assertEquals('resolved', $output['sections'][0]['items'][1]['type']);
        $this->assertEquals('Fixed item', $output['sections'][0]['items'][1]['fields'][0]['value']);

        $this->assertEquals('All current items', $output['sections'][1]['title']);
        $this->assertEquals('Still alert', $output['sections'][1]['items'][0]['fields'][0]['value']);
    }

    public function testFormatBill(): void
    {
        $details = [
            'rule' => [
                [
                    'bill_id' => 123,
                    'bill_name' => 'Test Bill',
                ],
            ],
        ];

        $output = $this->parser->parse($details);
        $fields = $output['sections'][0]['items'][0]['fields'];

        $this->assertEquals('Bill', $fields[0]['label']);
        $this->assertEquals('Test Bill', $fields[0]['value']);
        $this->assertStringContainsString('bill_id=123', $fields[0]['url']);
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
                ],
            ],
        ];

        $output = $this->parser->parse($details);
        $fields = $output['sections'][0]['items'][0]['fields'];

        $this->assertEquals('Port', $fields[0]['label']);
        $this->assertEquals('eth0', $fields[0]['value']);
        $this->assertEquals('Alias', $fields[1]['label']);
        $this->assertEquals('WAN Interface', $fields[1]['value']);
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
                    'sensor_descr' => 'CPU Temp',
                ],
            ],
        ];

        $output = $this->parser->parse($details);
        $fields = $output['sections'][0]['items'][0]['fields'];

        $this->assertEquals('Sensor', $fields[0]['label']);
        $this->assertEquals('CPU Temp', $fields[0]['value']);
        $this->assertEquals('Value', $fields[1]['label']);
        $this->assertStringContainsString('35', $fields[1]['value']);
        $this->assertStringContainsString('(temperature)', $fields[1]['value']);
        $this->assertEquals('Thresholds', $fields[2]['label']);
        $this->assertStringContainsString('High Warn: 38', $fields[2]['value']);
        $this->assertStringContainsString('High: 40', $fields[2]['value']);
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
                    'sensor_descr' => 'Power State',
                ],
            ],
        ];

        $output = $this->parser->parse($details);
        $fields = $output['sections'][0]['items'][0]['fields'];

        $this->assertEquals('Sensor', $fields[0]['label']);
        $this->assertEquals('Power State', $fields[0]['value']);
        $this->assertEquals('State', $fields[1]['label']);
        $this->assertStringContainsString('Critical (numerical: 2)', $fields[1]['value']);
    }

    public function testFormatAccessPoint(): void
    {
        $details = [
            'rule' => [
                [
                    'accesspoint_id' => 101,
                    'device_id' => 1,
                    'name' => 'AP-01',
                ],
            ],
        ];

        $output = $this->parser->parse($details);
        $fields = $output['sections'][0]['items'][0]['fields'];

        $this->assertEquals('Access Point', $fields[0]['label']);
        $this->assertEquals('AP-01', $fields[0]['value']);
        $this->assertStringContainsString('accesspoints', $fields[0]['url']);
        $this->assertStringContainsString('ap=101', $fields[0]['url']);
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
                    'service_message' => 'Connection refused',
                ],
            ],
        ];

        $output = $this->parser->parse($details);
        $fields = $output['sections'][0]['items'][0]['fields'];

        $this->assertEquals('Service', $fields[0]['label']);
        $this->assertEquals('HTTP (http)', $fields[0]['value']);
        $this->assertStringContainsString('services', $fields[0]['url']);
        $this->assertStringContainsString('view=detail', $fields[0]['url']);
        $this->assertEquals('Host', $fields[1]['label']);
        $this->assertEquals('1.2.3.4', $fields[1]['value']);
        $this->assertEquals('Description', $fields[2]['label']);
        $this->assertEquals('Web Service', $fields[2]['value']);
        $this->assertEquals('Message', $fields[3]['label']);
        $this->assertEquals('Connection refused', $fields[3]['value']);
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
                    'bgpPeerState' => 'idle',
                ],
            ],
        ];

        $output = $this->parser->parse($details);
        $fields = $output['sections'][0]['items'][0]['fields'];

        $this->assertEquals('BGP Peer', $fields[0]['label']);
        $this->assertEquals('10.0.0.1', $fields[0]['value']);
        $this->assertStringContainsString('routing', $fields[0]['url']);
        $this->assertStringContainsString('proto=bgp', $fields[0]['url']);
        $this->assertEquals('Description', $fields[1]['label']);
        $this->assertEquals('ISP-A', $fields[1]['value']);
        $this->assertEquals('Remote AS', $fields[2]['label']);
        $this->assertEquals('65001', $fields[2]['value']);
        $this->assertEquals('State', $fields[3]['label']);
        $this->assertEquals('idle', $fields[3]['value']);
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
                    'mempool_total' => 1024 * 1024 * 1024, // 1GB
                ],
            ],
        ];

        $output = $this->parser->parse($details);
        $fields = $output['sections'][0]['items'][0]['fields'];

        $this->assertEquals('Memory Pool', $fields[0]['label']);
        $this->assertEquals('System RAM', $fields[0]['value']);
        $this->assertStringContainsString('mempool_usage', $fields[0]['url']);
        $this->assertStringContainsString('404', $fields[0]['url']);
        $this->assertEquals('Usage', $fields[1]['label']);
        $this->assertStringContainsString('Usage: 85.5', $fields[1]['value']);
        $this->assertStringContainsString('Free: 104.86 MB', $fields[1]['value']);
        $this->assertStringContainsString('Total: 1.07 GB', $fields[1]['value']);
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
                    'value' => 5000,
                ],
            ],
        ];

        $output = $this->parser->parse($details);
        $fields = $output['sections'][0]['items'][0]['fields'];

        $this->assertEquals('Application', $fields[0]['label']);
        $this->assertEquals('nginx', $fields[0]['value']);
        $this->assertStringContainsString('apps', $fields[0]['url']);
        $this->assertStringContainsString('app=nginx', $fields[0]['url']);
        $this->assertEquals('Status', $fields[1]['label']);
        $this->assertEquals('up', $fields[1]['value']);
        $this->assertEquals('Metric', $fields[2]['label']);
        $this->assertEquals('requests = 5000', $fields[2]['value']);
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
                    'another_val' => 'present',
                    'sysContact' => 'admin@example.com', // should be included
                    'community' => 'public', // should be skipped
                    'snmpver' => 'v2c', // should be skipped
                    'authname' => 'user', // should be skipped (contains auth)
                    'cryptopass' => 'secret', // should be skipped (contains pass)
                ],
            ],
        ];

        $output = $this->parser->parse($details);
        $fields = $output['sections'][0]['items'][0]['fields'];

        $labels = array_column($fields, 'label');
        $this->assertContains('custom_key', $labels);
        $this->assertContains('another_val', $labels);
        $this->assertContains('sysContact', $labels);
        $this->assertNotContains('device_id', $labels);
        $this->assertNotContains('some_id', $labels);
        $this->assertNotContains('description', $labels);
        $this->assertNotContains('community', $labels);
        $this->assertNotContains('snmpver', $labels);
        $this->assertNotContains('authname', $labels);
        $this->assertNotContains('cryptopass', $labels);
    }
}
