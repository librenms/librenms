<?php

/**
 * ApiUnknownDeviceTest.php
 *
 * API endpoints should return 404 when a hostname or device ID is provided but
 * does not match any device, instead of silently dropping the device filter
 * (returning data of all devices) or failing with 500.
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

namespace LibreNMS\Tests\Feature;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ApiUnknownDeviceTest extends DBTestCase
{
    use DatabaseTransactions;

    private Device $device;

    /** @var array<string, string> */
    private array $headers = [];

    protected function setUp(): void
    {
        parent::setUp();

        /** @var User $user */
        $user = User::factory()->admin()->create();
        $token = $user->createToken('test');
        $this->headers = ['X-Auth-Token' => $token->plainTextToken];

        $this->device = Device::factory()->create(['hostname' => 'existing.example.test']);
    }

    #[DataProvider('unknownDeviceListEndpoints')]
    public function testListEndpointReturns404ForUnknownDeviceId(string $uri): void
    {
        $this->json('GET', $uri . '?hostname=99999', [], $this->headers)
            ->assertStatus(404)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Device 99999 not found');
    }

    #[DataProvider('unknownDeviceListEndpoints')]
    public function testListEndpointReturns404ForUnknownHostname(string $uri): void
    {
        $this->json('GET', $uri . '?hostname=unknown.example.test', [], $this->headers)
            ->assertStatus(404)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Device unknown.example.test not found');
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function unknownDeviceListEndpoints(): array
    {
        return [
            'bgp' => ['/api/v0/bgp'],
            'bgp counters' => ['/api/v0/routing/bgp/cbgp'],
            'ospf' => ['/api/v0/ospf'],
            'vrf' => ['/api/v0/routing/vrf'],
            'mpls services' => ['/api/v0/routing/mpls/services'],
            'mpls saps' => ['/api/v0/routing/mpls/saps'],
            'vlans' => ['/api/v0/resources/vlans'],
        ];
    }

    public function testBgpWithoutHostnameStillListsEverything(): void
    {
        $this->json('GET', '/api/v0/bgp', [], $this->headers)
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok');
    }

    public function testEventlogReturns404ForUnknownDevice(): void
    {
        $this->json('GET', '/api/v0/logs/eventlog/99999', [], $this->headers)
            ->assertStatus(404)
            ->assertJsonPath('message', 'Device 99999 not found');

        $this->json('GET', '/api/v0/logs/eventlog', [], $this->headers)
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok');
    }

    public function testServicesReturns404ForUnknownDevice(): void
    {
        $this->json('GET', '/api/v0/services/99999', [], $this->headers)
            ->assertStatus(404)
            ->assertJsonPath('message', 'Device 99999 not found');

        $this->json('GET', '/api/v0/services', [], $this->headers)
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok');
    }

    public function testLinksReturns404ForUnknownDevice(): void
    {
        $this->json('GET', '/api/v0/devices/99999/links', [], $this->headers)
            ->assertStatus(404)
            ->assertJsonPath('message', 'Device 99999 not found');
    }

    public function testIpsecReturns400ForUnknownDevice(): void
    {
        $this->json('GET', '/api/v0/routing/ipsec/data/99999', [], $this->headers)
            ->assertStatus(400)
            ->assertJsonPath('message', 'No valid hostname or device ID provided');
    }

    public function testMaintenanceReturns404ForUnknownDevice(): void
    {
        $this->json('GET', '/api/v0/devices/99999/maintenance', [], $this->headers)
            ->assertStatus(404)
            ->assertJsonPath('message', 'Device 99999 not found');
    }

    public function testInventoryReturns400ForUnknownDevice(): void
    {
        $this->json('GET', '/api/v0/inventory/99999', [], $this->headers)
            ->assertStatus(400)
            ->assertJsonPath('message', 'Invalid device provided');
    }

    public function testParentsReturns400ForUnknownDevice(): void
    {
        $this->json('POST', '/api/v0/devices/99999/parents', ['parent_ids' => ''], $this->headers)
            ->assertStatus(400)
            ->assertJsonPath('message', 'Check your parent and device IDs');

        $this->json('DELETE', '/api/v0/devices/99999/parents', [], $this->headers)
            ->assertStatus(400)
            ->assertJsonPath('message', 'Check your device ID!');
    }

    public function testAddParentsStillWorksForExistingDevice(): void
    {
        $parent = Device::factory()->create(['hostname' => 'parent.example.test']);

        $this->json('POST', "/api/v0/devices/{$this->device->device_id}/parents", ['parent_ids' => $parent->hostname], $this->headers)
            ->assertStatus(201)
            ->assertJsonPath('status', 'ok');

        $this->assertTrue($this->device->fresh()->parents()->whereKey($parent->device_id)->exists());

        $this->json('DELETE', "/api/v0/devices/{$this->device->device_id}/parents", ['parent_ids' => $parent->hostname], $this->headers)
            ->assertStatus(201)
            ->assertJsonPath('status', 'ok');

        $this->assertFalse($this->device->fresh()->parents()->whereKey($parent->device_id)->exists());
    }

    public function testAddRuleReturns400ForUnknownDevice(): void
    {
        foreach (['99999', 'unknown.example.test'] as $bad_device) {
            $this->json('POST', '/api/v0/rules', [
                'devices' => [$bad_device],
                'name' => 'unknown device rule',
                'severity' => 'critical',
                'builder' => self::alertRuleBuilder(),
            ], $this->headers)->assertStatus(400)->assertJsonPath('message', "Device $bad_device does not exist");
        }
    }

    public function testAddRuleStillWorksForExistingDevice(): void
    {
        $this->json('POST', '/api/v0/rules', [
            'devices' => [$this->device->device_id],
            'name' => 'existing device rule',
            'severity' => 'critical',
            'builder' => self::alertRuleBuilder(),
        ], $this->headers)->assertStatus(200);
    }

    /**
     * @return array<string, mixed>
     */
    private static function alertRuleBuilder(): array
    {
        return [
            'condition' => 'AND',
            'rules' => [[
                'id' => 'devices.hostname',
                'field' => 'devices.hostname',
                'type' => 'string',
                'input' => 'text',
                'operator' => 'equal',
                'value' => 'localhost',
            ]],
            'valid' => true,
        ];
    }
}
