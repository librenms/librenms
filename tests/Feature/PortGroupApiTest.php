<?php

namespace LibreNMS\Tests\Feature;

use App\Models\ApiToken;
use App\Models\Device;
use App\Models\Port;
use App\Models\PortGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;

final class PortGroupApiTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testCreateStaticPortGroup(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id]);

        $this->json('POST', '/api/v0/port_groups', [
            'name' => 'Static Group',
            'desc' => 'manually managed',
            'type' => 'static',
            'ports' => [$port->port_id],
        ], $this->apiHeaders())
            ->assertStatus(201);

        $group = PortGroup::where('name', 'Static Group')->firstOrFail();
        $this->assertSame('static', $group->type);
        $this->assertEquals([$port->port_id], $group->ports()->pluck('ports.port_id')->all());
    }

    public function testCreateWithoutTypeDefaultsToStatic(): void
    {
        $this->json('POST', '/api/v0/port_groups', [
            'name' => 'Legacy Group',
            'desc' => 'created without a type, as older API clients do',
        ], $this->apiHeaders())
            ->assertStatus(201);

        $this->assertSame('static', PortGroup::where('name', 'Legacy Group')->firstOrFail()->type);
    }

    public function testCreateDynamicPortGroupSyncsMatchingPorts(): void
    {
        $device = Device::factory()->create();
        $uplink = Port::factory()->create(['device_id' => $device->device_id, 'ifAlias' => 'uplink to core']);
        Port::factory()->create(['device_id' => $device->device_id, 'ifAlias' => 'access port']);

        $this->json('POST', '/api/v0/port_groups', [
            'name' => 'Uplinks',
            'desc' => 'all uplink ports',
            'type' => 'dynamic',
            'rules' => json_encode($this->rulesFor('ports.ifAlias', 'begins_with', 'uplink')),
        ], $this->apiHeaders())
            ->assertStatus(201);

        $group = PortGroup::where('name', 'Uplinks')->firstOrFail();
        $this->assertSame('dynamic', $group->type);
        $this->assertEquals([$uplink->port_id], $group->ports()->pluck('ports.port_id')->all());
    }

    public function testCreateDynamicPortGroupRequiresRules(): void
    {
        $this->json('POST', '/api/v0/port_groups', [
            'name' => 'No Rules',
            'desc' => 'dynamic without rules',
            'type' => 'dynamic',
        ], $this->apiHeaders())
            ->assertStatus(422);
    }

    public function testAssignAndRemoveAreRejectedForDynamicGroups(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id]);
        $group = PortGroup::factory()->create([
            'type' => 'dynamic',
            'rules' => $this->rulesFor('ports.ifAlias', 'begins_with', 'uplink'),
        ]);

        $headers = $this->apiHeaders();

        $this->json('POST', "/api/v0/port_groups/{$group->id}/assign", [
            'port_ids' => [$port->port_id],
        ], $headers)
            ->assertStatus(400);

        $this->json('POST', "/api/v0/port_groups/{$group->id}/remove", [
            'port_ids' => [$port->port_id],
        ], $headers)
            ->assertStatus(400);
    }

    public function testAssignAndRemovePortsOnStaticGroup(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id]);
        $group = PortGroup::factory()->create(); // static

        $headers = $this->apiHeaders();

        $this->json('POST', "/api/v0/port_groups/{$group->id}/assign", [
            'port_ids' => [$port->port_id],
        ], $headers)
            ->assertStatus(200);
        $this->assertEquals([$port->port_id], $group->ports()->pluck('ports.port_id')->all());

        $this->json('POST', "/api/v0/port_groups/{$group->id}/remove", [
            'port_ids' => [$port->port_id],
        ], $headers)
            ->assertStatus(200);
        $this->assertSame(0, $group->ports()->count());
    }

    /**
     * @return array<string, string>
     */
    private function apiHeaders(): array
    {
        $user = User::factory()->admin()->create();
        $token = ApiToken::generateToken($user);

        return ['X-Auth-Token' => $token->token_hash];
    }

    private function rulesFor(string $field, string $operator, string $value): array
    {
        return [
            'condition' => 'AND',
            'rules' => [
                [
                    'id' => $field,
                    'field' => $field,
                    'type' => 'string',
                    'input' => 'text',
                    'operator' => $operator,
                    'value' => $value,
                ],
            ],
            'valid' => true,
        ];
    }
}
