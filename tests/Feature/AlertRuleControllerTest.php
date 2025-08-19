<?php
/**
 * AlertRuleControllerTest.php
 *
 * -Description-
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
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Feature;

use App\Models\AlertRule;
use App\Models\AlertTransport;
use App\Models\AlertTransportGroup;
use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use LibreNMS\Tests\DBTestCase;

class AlertRuleControllerTest extends DBTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure validation and models use the testing connection
        config(['database.default' => 'testing']);
    }

    private function actAsAdmin(): User
    {
        $user = User::factory()->create();
        // enable and assign admin role
        $user->enabled = 1;
        $user->save();
        $user->assignRole('admin');
        $this->actingAs($user);

        return $user;
    }

    private function sampleBuilderJson(): string
    {
        // Simple valid builder taken from tests/data/misc/querybuilder.json
        return json_encode([
            'condition' => 'AND',
            'rules' => [
                [
                    'id' => 'devices.hostname',
                    'field' => 'devices.hostname',
                    'type' => 'string',
                    'input' => 'text',
                    'operator' => 'contains',
                    'value' => 'contains',
                ],
            ],
            'valid' => true,
        ]);
    }

    public function testStoreWithBuilderJsonCreatesRuleAndSyncsRelations(): void
    {
        $this->actAsAdmin();

        // create related models
        $device = Device::factory()->create();
        $group = new DeviceGroup(['name' => 'DG_' . $this->faker->unique()->bothify('??????????'), 'type' => 'static']);
        $group->save();
        $location = Location::factory()->create();
        $transport = AlertTransport::factory()->create();

        // no factory exists for AlertTransportGroup, create raw
        $tg = new AlertTransportGroup(['transport_group_name' => 'TG_' . $this->faker->unique()->bothify('??????????')]);
        $tg->save();

        $payload = [
            'name' => 'Rule ' . $this->faker->unique()->uuid(),
            'severity' => 'critical',
            'builder_json' => $this->sampleBuilderJson(),
            'count' => '3',
            'delay' => '10m',
            'interval' => '1h',
            'mute' => true,
            'invert' => false,
            'recovery' => true,
            'acknowledgement' => false,
            'invert_map' => false,
            'maps' => [
                (string) $device->device_id,
                'g' . $group->id,
                'l' . $location->id,
            ],
            'transports' => [
                (string) $transport->transport_id,
                'g' . $tg->transport_group_id,
            ],
            'notes' => 'note here',
            'proc' => 'proc.php',
        ];

        $response = $this->postJson('/alert-rule', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ])
            ->assertSee('Added Rule: <i>' . $payload['name'] . '</i>', false);

        $rule = AlertRule::query()->where('name', $payload['name'])->firstOrFail();

        $this->assertEquals('critical', $rule->severity);
        $this->assertEquals('proc.php', $rule->proc);
        $this->assertEquals('note here', $rule->notes);
        $this->assertIsArray($rule->builder);
        $this->assertIsArray($rule->extra);

        $this->assertSame('3', $rule->extra['count']);
        $this->assertSame(600, $rule->extra['delay']); // 10m -> 600s
        $this->assertSame(3600, $rule->extra['interval']); // 1h -> 3600s
        $this->assertTrue($rule->extra['mute']);
        $this->assertFalse($rule->extra['invert']);
        $this->assertTrue($rule->extra['recovery']);
        $this->assertFalse($rule->extra['acknowledgement']);
        $this->assertEquals(['override_query' => null], $rule->extra['options']);

        // relationships
        $this->assertTrue($rule->devices()->where('devices.device_id', $device->device_id)->exists());
        $this->assertTrue($rule->groups()->where('device_groups.id', $group->id)->exists());
        $this->assertTrue($rule->locations()->where('locations.id', $location->id)->exists());
        $this->assertTrue($rule->transportSingles()->where('alert_transports.transport_id', $transport->transport_id)->exists());
        $this->assertTrue($rule->transportGroups()->where('alert_transport_groups.transport_group_id', $tg->transport_group_id)->exists());
    }

    public function testStoreWithOverrideQueryCreatesRule(): void
    {
        $this->actAsAdmin();

        $payload = [
            'name' => 'Rule ' . $this->faker->unique()->uuid(),
            'severity' => 'warning',
            'override_query' => 'on',
            'adv_query' => 'SELECT * FROM devices WHERE devices.device_id = 1',
            'count' => '1',
        ];

        $response = $this->postJson('/alert-rule', $payload);
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ])
            ->assertSee('Added Rule: <i>' . $payload['name'] . '</i>', false);

        $rule = AlertRule::query()->where('name', $payload['name'])->firstOrFail();
        $this->assertEquals('warning', $rule->severity);
        $this->assertEquals('SELECT * FROM devices WHERE devices.device_id = 1', $rule->query);
        $this->assertEquals(['override_query' => 'on'], $rule->extra['options']);
    }

    public function testStoreWithoutRulesReturnsError(): void
    {
        $this->actAsAdmin();

        $payload = [
            'name' => 'Rule ' . $this->faker->unique()->uuid(),
            'severity' => 'critical',
            // no builder_json and no override_query
        ];

        $response = $this->postJson('/alert-rule', $payload);
        // Request validation will fail builder_json required => 422
        $response->assertStatus(422);
    }

    public function testInvertMapRequiresMapsValidation(): void
    {
        $this->actAsAdmin();

        $payload = [
            'name' => 'Rule ' . $this->faker->unique()->uuid(),
            'severity' => 'critical',
            'builder_json' => $this->sampleBuilderJson(),
            'invert_map' => true,
            // maps missing => should 422 with message from AlertRuleRequest
        ];

        $response = $this->postJson('/alert-rule', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['maps']);
    }

    public function testUniqueNameOnlyOnStore(): void
    {
        $this->actAsAdmin();

        $name = 'Rule ' . $this->faker->unique()->uuid();

        $payload = [
            'name' => $name,
            'severity' => 'info',
            'builder_json' => $this->sampleBuilderJson(),
        ];
        $this->postJson('/alert-rule', $payload)->assertStatus(200);

        // duplicate on store should fail
        $this->postJson('/alert-rule', $payload)->assertStatus(422);

        // update existing with same name should succeed
        $rule = AlertRule::query()->where('name', $name)->firstOrFail();
        $payloadUpdate = [
            'name' => $name,
            'severity' => 'info',
            'builder_json' => $this->sampleBuilderJson(),
        ];
        $this->putJson('/alert-rule/' . $rule->getKey(), $payloadUpdate)
            ->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'message' => 'Edited Rule: <i>' . $name . '</i>',
            ]);
    }

    public function testUpdateEditsRuleAndSyncsRelations(): void
    {
        $this->actAsAdmin();

        $name = 'Rule ' . $this->faker->unique()->uuid();

        // create initial rule via store
        $initial = [
            'name' => $name,
            'severity' => 'critical',
            'builder_json' => $this->sampleBuilderJson(),
            'maps' => [],
            'transports' => [],
        ];
        $this->postJson('/alert-rule', $initial)->assertStatus(200);
        $rule = AlertRule::query()->where('name', $name)->firstOrFail();

        // create related models for update
        $device = Device::factory()->create();
        $group = new DeviceGroup(['name' => 'DG_' . $this->faker->unique()->bothify('??????????'), 'type' => 'static']);
        $group->save();
        $location = Location::factory()->create();
        $transport = AlertTransport::factory()->create();
        $tg = new AlertTransportGroup(['transport_group_name' => 'TG_' . $this->faker->unique()->bothify('??????????')]);
        $tg->save();

        $update = [
            'name' => $name,
            'severity' => 'warning',
            'builder_json' => $this->sampleBuilderJson(),
            'count' => '7',
            'delay' => '10', // seconds
            'interval' => '2h',
            'invert' => true,
            'recovery' => false,
            'acknowledgement' => true,
            'invert_map' => true,
            'maps' => [
                (string) $device->device_id,
                'g' . $group->id,
                'l' . $location->id,
            ],
            'transports' => [
                (string) $transport->transport_id,
                'g' . $tg->transport_group_id,
            ],
            'notes' => 'updated note',
            'proc' => 'updated.php',
        ];

        $resp = $this->putJson('/alert-rule/' . $rule->getKey(), $update);
        $resp->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'message' => 'Edited Rule: <i>' . $name . '</i>',
            ]);

        $rule->refresh();
        $this->assertEquals('warning', $rule->severity);
        $this->assertSame('7', $rule->extra['count']);
        $this->assertSame(10, $rule->extra['delay']);
        $this->assertSame(7200, $rule->extra['interval']);
        $this->assertTrue($rule->extra['invert']);
        $this->assertFalse($rule->extra['recovery']);
        $this->assertTrue($rule->extra['acknowledgement']);
        $this->assertTrue($rule->invert_map);

        $this->assertTrue($rule->devices()->where('devices.device_id', $device->device_id)->exists());
        $this->assertTrue($rule->groups()->where('device_groups.id', $group->id)->exists());
        $this->assertTrue($rule->locations()->where('locations.id', $location->id)->exists());
        $this->assertTrue($rule->transportSingles()->where('alert_transports.transport_id', $transport->transport_id)->exists());
        $this->assertTrue($rule->transportGroups()->where('alert_transport_groups.transport_group_id', $tg->transport_group_id)->exists());
    }
}
