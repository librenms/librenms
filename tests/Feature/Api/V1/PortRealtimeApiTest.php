<?php

namespace LibreNMS\Tests\Feature\Api\V1;

use App\Actions\Port\FetchPortOctets;
use App\Facades\LibrenmsConfig;
use App\Http\Middleware\EnforceJsonApi;
use App\Models\Device;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use LibreNMS\Tests\DBTestCase;
use Mockery;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class PortRealtimeApiTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        LibrenmsConfig::set('api.v1.enabled', true);
    }

    public function testReturnsTheCurrentCounters(): void
    {
        $port = Port::factory()->for(Device::factory())->create();
        $this->fakeOctets(['18446744073709551000', '42']);
        Sanctum::actingAs($this->apiUser('admin'));

        $this->getJson("/api/v1/ports/$port->port_id/realtime")
            ->assertOk()
            ->assertHeader('Content-Type', EnforceJsonApi::CONTENT_TYPE)
            ->assertJsonPath('data.type', 'port-realtime')
            ->assertJsonPath('data.id', (string) $port->port_id)
            ->assertJsonPath('data.attributes.in_octets', '18446744073709551000')
            ->assertJsonPath('data.attributes.out_octets', '42');
    }

    public function testCountersAreNullWhenTheDeviceDoesNotAnswer(): void
    {
        $port = Port::factory()->for(Device::factory())->create();
        $this->fakeOctets(['', '']);
        Sanctum::actingAs($this->apiUser('admin'));

        $this->getJson("/api/v1/ports/$port->port_id/realtime")
            ->assertOk()
            ->assertJsonPath('data.attributes.in_octets', null)
            ->assertJsonPath('data.attributes.out_octets', null);
    }

    public function testUsersNeedAccessToThePort(): void
    {
        $port = Port::factory()->for(Device::factory())->create();
        $this->fakeOctets(null);
        Sanctum::actingAs($this->apiUser('user'));

        $this->getJson("/api/v1/ports/$port->port_id/realtime")
            ->assertForbidden()
            ->assertJsonPath('errors.0.status', '403');
    }

    public function testUnknownPortsAreNotFound(): void
    {
        $this->fakeOctets(null);
        Sanctum::actingAs($this->apiUser('admin'));

        $this->getJson('/api/v1/ports/999999/realtime')
            ->assertNotFound()
            ->assertJsonPath('errors.0.status', '404');
    }

    public function testATokenIsRequired(): void
    {
        $port = Port::factory()->for(Device::factory())->create();
        $this->fakeOctets(null);

        $this->getJson("/api/v1/ports/$port->port_id/realtime")->assertUnauthorized();
    }

    private function apiUser(string $role): User
    {
        $user = User::factory()->create(['enabled' => 1])->assignRole(Role::findOrCreate($role));
        $user->givePermissionTo(Permission::findOrCreate('api.access'));

        return $user;
    }

    /**
     * @param  array{string, string}|null  $octets  null when the device must not be queried
     */
    private function fakeOctets(?array $octets): void
    {
        $fetch = Mockery::mock(FetchPortOctets::class);
        if ($octets === null) {
            $fetch->shouldNotReceive('execute');
        } else {
            $fetch->shouldReceive('execute')->once()->andReturn($octets);
        }
        $this->app->instance(FetchPortOctets::class, $fetch);
    }
}
