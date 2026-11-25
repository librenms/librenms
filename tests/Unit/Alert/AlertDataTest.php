<?php

/**
 * AlertDataTest.php
 *
 * Tests for AlertData DTO
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 LibreNMS
 */

namespace LibreNMS\Tests\Unit\Alert;

use App\Models\AlertTemplate;
use App\Models\Device;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Blade;
use LibreNMS\Alert\AlertData;
use LibreNMS\Alert\Template;
use LibreNMS\Enum\AlertState;
use LibreNMS\Tests\TestCase;

final class AlertDataTest extends TestCase
{
    public function testConstructFromArray(): void
    {
        $data = [
            'hostname' => 'router1.example.com',
            'device_id' => 42,
            'sysDescr' => 'Cisco Router',
            'sysName' => 'router1',
            'sysContact' => 'admin@example.com',
            'os' => 'ios',
            'type' => 'network',
            'ip' => '192.0.2.1',
            'display' => 'router1.example.com',
            'version' => '15.1',
            'hardware' => 'Cisco 2901',
            'features' => 'IPBASE',
            'serial' => 'FOC12345678',
            'status' => true,
            'status_reason' => 'normal',
            'location' => 'DataCenter',
            'description' => 'Core Router',
            'notes' => 'Some notes',
            'alert_notes' => 'Alert note',
            'uptime' => 3600,
            'uptime_short' => '1h',
            'uptime_long' => '1 hour',
            'title' => 'Device Down',
            'elapsed' => '5m',
            'alerted' => 1,
            'alert_id' => 10,
            'rule_id' => 2,
            'id' => 100,
            'proc' => 'Procedure text',
            'faults' => [['id' => 1, 'string' => 'fault 1']],
            'uid' => 100,
            'severity' => 'critical',
            'rule' => 'device.status = 0',
            'name' => 'Device Down Rule',
            'string' => '#1: fault',
            'timestamp' => '2026-09-15 12:00:00',
            'contacts' => ['admin@example.com' => 'Admin'],
            'state' => AlertState::ACTIVE,
            'msg' => 'Device is down',
            'builder' => '{"condition":"AND"}',
            'device_groups' => [1 => 'Routers'],
            'ping_timestamp' => CarbonImmutable::createFromTimestamp(1600000000),
            'ping_loss' => 0.0,
            'ping_min' => 1.2,
            'ping_max' => 3.4,
            'ping_avg' => 2.1,
            'debug' => 'unsupported',
            'diff' => ['added' => []],
            'transport' => 'mail',
            'transport_name' => 'Default Mail',
        ];

        $alert = new AlertData($data);

        $this->assertSame('router1.example.com', $alert->hostname);
        $this->assertSame(42, $alert->device_id);
        $this->assertSame('Cisco Router', $alert->sysDescr);
        $this->assertSame('router1', $alert->sysName);
        $this->assertSame('admin@example.com', $alert->sysContact);
        $this->assertSame('ios', $alert->os);
        $this->assertSame('network', $alert->type);
        $this->assertSame('192.0.2.1', $alert->ip);
        $this->assertSame('router1.example.com', $alert->display);
        $this->assertSame('15.1', $alert->version);
        $this->assertSame('Cisco 2901', $alert->hardware);
        $this->assertSame('IPBASE', $alert->features);
        $this->assertSame('FOC12345678', $alert->serial);
        $this->assertTrue($alert->status);
        $this->assertSame('normal', $alert->status_reason);
        $this->assertSame('DataCenter', $alert->location);
        $this->assertSame('Core Router', $alert->description);
        $this->assertSame('Some notes', $alert->notes);
        $this->assertSame('Alert note', $alert->alert_notes);
        $this->assertSame(3600, $alert->uptime);
        $this->assertSame('1h', $alert->uptime_short);
        $this->assertSame('1 hour', $alert->uptime_long);
        $this->assertSame('Device Down', $alert->title);
        $this->assertSame('5m', $alert->elapsed);
        $this->assertSame(1, $alert->alerted);
        $this->assertSame(10, $alert->alert_id);
        $this->assertSame(2, $alert->rule_id);
        $this->assertSame(100, $alert->id);
        $this->assertSame('Procedure text', $alert->proc);
        $this->assertCount(1, $alert->faults);
        $this->assertSame(100, $alert->uid);
        $this->assertSame('critical', $alert->severity);
        $this->assertSame('device.status = 0', $alert->rule);
        $this->assertSame('Device Down Rule', $alert->name);
        $this->assertSame('#1: fault', $alert->string);
        $this->assertSame('2026-09-15 12:00:00', $alert->timestamp);
        $this->assertSame(['admin@example.com' => 'Admin'], $alert->contacts);
        $this->assertSame(AlertState::ACTIVE, $alert->state);
        $this->assertSame('Device is down', $alert->msg);
        $this->assertSame('{"condition":"AND"}', $alert->builder);
        $this->assertSame([1 => 'Routers'], $alert->device_groups);
        $this->assertSame(1600000000, $alert->ping_timestamp?->getTimestamp());
        $this->assertSame(0.0, $alert->ping_loss);
        $this->assertSame(1.2, $alert->ping_min);
        $this->assertSame(3.4, $alert->ping_max);
        $this->assertSame(2.1, $alert->ping_avg);
        $this->assertSame('unsupported', $alert->debug);
        $this->assertSame(['added' => []], $alert->diff);
        $this->assertSame('mail', $alert->transport);
        $this->assertSame('Default Mail', $alert->transport_name);
    }

    public function testTemplatesCanUseArrayAccess(): void
    {
        $alert = new AlertData(['hostname' => 'router1', 'faults' => [1 => ['string' => 'port down']]]);

        $rendered = Blade::render(
            '{{ $alert[\'hostname\'] }} {{ $alert[\'faults\'][1][\'string\'] }}@if ($alert[\'typo\']) typo @endif {{ $alert[\'severity\'] ?? \'none\' }}',
            ['alert' => $alert]
        );

        $this->assertSame('router1 port down none', $rendered);
    }

    public function testArrayWritesThrow(): void
    {
        $alert = new AlertData(['title' => 'Initial Title']);

        try {
            $alert['title'] = 'Updated Title';
            $this->fail('offsetSet should throw');
        } catch (\LogicException) {
        }

        try {
            unset($alert['title']);
            $this->fail('offsetUnset should throw');
        } catch (\LogicException) {
        }

        $this->assertSame('Initial Title', $alert->title);
    }

    public function testUnknownFieldThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AlertData(['hostname' => 'server1', 'severty' => 'critical']);
    }

    public function testPropertyWritesThrow(): void
    {
        $alert = new AlertData(['title' => 'Initial Title']);

        $this->expectException(\Error::class);
        $alert->title = 'Updated Title'; // @phpstan-ignore assign.propertyPrivateSet
    }

    public function testForTransportRendersTitleThenBody(): void
    {
        $alert = new AlertData(['hostname' => 'router1', 'state' => AlertState::ACTIVE]);
        $template = new Template(new AlertTemplate([
            'title' => '{{ $alert->hostname }} via {{ $alert->transport }}',
            'template' => '{{ $alert->title }} ({{ $alert->transport_name }})',
        ]));

        $forTransport = $alert->forTransport('mail', 'Ops Mail', $template);

        $this->assertNotSame($alert, $forTransport);
        $this->assertNull($alert->transport);
        $this->assertNull($alert->title);
        $this->assertSame('mail', $forTransport->transport);
        $this->assertSame('Ops Mail', $forTransport->transport_name);
        $this->assertSame('router1 via mail', $forTransport->title);
        $this->assertSame('router1 via mail (Ops Mail)', $forTransport->msg);
    }

    public function testTransportPayloadIncludesTemplateAndAlert(): void
    {
        $alert = new AlertData(['hostname' => 'router1', 'msg' => 'down']);
        $template = new AlertTemplate(['name' => 'Custom']);

        $payload = $alert->toTransportPayload($template);

        $this->assertSame('router1', $payload['hostname']);
        $this->assertSame('down', $payload['msg']);
        $this->assertSame($template, $payload['template']);
        $this->assertSame($alert, $payload['alert']);
    }

    public function testUndefinedPropertyFallback(): void
    {
        $alert = new AlertData();

        $this->assertSame('non_existent is not a valid $alert data name', $alert->__get('non_existent'));
        $this->assertFalse(isset($alert->non_existent));
        $this->assertSame('fallback', $alert->non_existent ?? 'fallback');
        $this->assertNull($alert->hostname ?? null);
    }

    public function testJsonSerializeOnlyIncludesProvidedFields(): void
    {
        $alert = new AlertData(['hostname' => 'host1', 'device_id' => 10, 'title' => null]);

        $json = json_encode($alert);
        $this->assertIsString($json);
        $decoded = json_decode($json, true);
        $this->assertSame('host1', $decoded['hostname']);
        $this->assertSame(10, $decoded['device_id']);
        $this->assertArrayHasKey('title', $decoded);
        $this->assertNull($decoded['title']);
        $this->assertArrayNotHasKey('faults', $decoded);
    }

    public function testTestData(): void
    {
        $device = new Device([
            'hostname' => 'test-device',
            'sysDescr' => 'Linux 5.4.0',
            'os' => 'linux',
            'display' => 'test-device',
        ]);
        $device->device_id = 99;

        $alert = AlertData::testData($device);

        $this->assertSame('test-device', $alert->hostname);
        $this->assertSame(99, $alert->device_id);
        $this->assertSame('linux', $alert->os);
        $this->assertSame(0, $alert->uid);
    }

    public function testBladeRenderingWithAlertData(): void
    {
        $alert = new AlertData([
            'hostname' => 'core-router.net',
            'severity' => 'critical',
            'state' => AlertState::ACTIVE,
            'title' => 'Core Router Down',
        ]);

        $rendered = Blade::render('Alert: {{ $alert->hostname }} ({{ $alert->severity }})', ['alert' => $alert]);
        $this->assertSame('Alert: core-router.net (critical)', $rendered);
    }

    public function testTemplateMethodsAcceptAlertData(): void
    {
        $alert = new AlertData([
            'hostname' => 'core-router.net',
            'severity' => 'critical',
            'state' => AlertState::ACTIVE,
            'display' => 'core-router',
            'name' => 'Down Rule',
            'uid' => '123',
            'rule' => 'macros.device = 1',
            'faults' => [['string' => 'device unreachable']],
            'contacts' => ['admin@example.com' => 'Admin'],
        ]);

        $template = new Template(new AlertTemplate(['name' => 'Default Alert Template']));
        $alert = $alert->forTransport(null, null, $template);
        $this->assertSame('Alert for device core-router - Down Rule', $alert->title);

        $body = $alert->msg;
        $this->assertStringContainsString('Alert for device core-router - Down Rule', $body);
        $this->assertStringContainsString('Severity: critical', $body);
        $this->assertStringContainsString('Unique-ID: 123', $body);
    }
}
