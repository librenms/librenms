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

use App\Models\Device;
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
            'status' => 1,
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
            'ping_timestamp' => 1600000000,
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
        $this->assertSame(1, $alert->status);
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
        $this->assertSame(1600000000, $alert->ping_timestamp);
        $this->assertSame(0.0, $alert->ping_loss);
        $this->assertSame(1.2, $alert->ping_min);
        $this->assertSame(3.4, $alert->ping_max);
        $this->assertSame(2.1, $alert->ping_avg);
        $this->assertSame('unsupported', $alert->debug);
        $this->assertSame(['added' => []], $alert->diff);
        $this->assertSame('mail', $alert->transport);
        $this->assertSame('Default Mail', $alert->transport_name);
    }

    public function testConstructFromSelf(): void
    {
        $original = new AlertData(['hostname' => 'switch1', 'device_id' => 5]);
        $copy = new AlertData($original);

        $this->assertSame('switch1', $copy->hostname);
        $this->assertSame(5, $copy->device_id);
    }

    public function testFromArrayStaticMethod(): void
    {
        $alert = AlertData::fromArray(['hostname' => 'switch2', 'severity' => 'warning']);

        $this->assertSame('switch2', $alert->hostname);
        $this->assertSame('warning', $alert->severity);
    }

    public function testArrayAccess(): void
    {
        $alert = new AlertData(['hostname' => 'server1', 'title' => 'Initial Title']);

        $this->assertTrue(isset($alert['hostname']));
        $this->assertSame('server1', $alert['hostname']);

        $alert['title'] = 'Updated Title';
        $this->assertSame('Updated Title', $alert->title);
        $this->assertSame('Updated Title', $alert['title']);

        unset($alert['title']);
        $this->assertNull($alert->title);
        $this->assertFalse(isset($alert['title']));
    }

    public function testUndefinedPropertyFallback(): void
    {
        $alert = new AlertData();

        $this->assertSame('non_existent is not a valid $alert data name', $alert->__get('non_existent'));
        $this->assertSame('non_existent is not a valid $alert data name', $alert['non_existent']);
        $this->assertFalse($alert->__isset('non_existent'));
        $this->assertFalse(isset($alert['non_existent']));
    }

    public function testToArrayAndJsonSerialize(): void
    {
        $alert = new AlertData(['hostname' => 'host1', 'device_id' => 10]);

        $array = $alert->toArray();
        $this->assertIsArray($array);
        $this->assertSame('host1', $array['hostname']);
        $this->assertSame(10, $array['device_id']);

        $json = json_encode($alert);
        $this->assertIsString($json);
        $decoded = json_decode($json, true);
        $this->assertSame('host1', $decoded['hostname']);
        $this->assertSame(10, $decoded['device_id']);
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

        $testData = AlertData::testData($device);

        $this->assertIsArray($testData);
        $this->assertSame('test-device', $testData['hostname']);
        $this->assertSame(99, $testData['device_id']);
        $this->assertSame('linux', $testData['os']);

        $alert = new AlertData($testData);
        $this->assertSame('test-device', $alert->hostname);
        $this->assertSame(99, $alert->device_id);
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
            'title' => 'Core Router Down',
            'name' => 'Down Rule',
            'uid' => '123',
            'rule' => 'macros.device = 1',
            'faults' => [['string' => 'device unreachable']],
            'contacts' => ['admin@example.com' => 'Admin'],
        ]);

        $template = new Template;
        $title = $template->getTitle($alert);
        $this->assertSame('Core Router Down', $title);

        $body = $template->getBody($alert);
        $this->assertStringContainsString('Core Router Down', $body);
        $this->assertStringContainsString('Severity: critical', $body);
        $this->assertStringContainsString('Unique-ID: 123', $body);
    }
}
