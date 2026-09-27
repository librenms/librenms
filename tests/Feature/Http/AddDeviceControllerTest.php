<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Secret;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\JsonResponse;
use Illuminate\Testing\TestResponse;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Data\Source\Icmp\FpingResponse;
use LibreNMS\Data\Source\Snmp\RawSnmpResponse;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Tests\DBTestCase;
use Mockery;
use Mockery\MockInterface;
use Spatie\Permission\Models\Permission;

final class AddDeviceControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('device.create');
        $this->admin = User::factory()->admin()->create(['enabled' => 1]);
    }

    public function testIndexProvidesDefaultDisplayTemplate(): void
    {
        LibrenmsConfig::set('device_display_default', '{{ $hostname }} - {{ $sysName }}');

        $this->actingAs($this->admin)->get(route('device.add'))
            ->assertOk()
            ->assertViewHas('default_display_template', '{{ $hostname }} - {{ $sysName }}')
            ->assertSee('name="display_template"', false)
            ->assertSee('id="secret-select-snmp"', false)
            ->assertSee('name="polling_methods[snmp][secret_id]"', false)
            ->assertSee('id="os-select"', false);
    }

    public function testStoreWithoutCheckingSavesDeviceAndSettings(): void
    {
        [$default] = $this->defaultSecrets('public');
        $this->snmpBackend()->shouldNotReceive('get');

        $response = $this->store('unchecked.example.com', [
            'snmp' => [
                'validate' => '0',
                'settings' => ['transport' => 'udp', 'port' => '', 'port_association_mode' => 'ifName'],
            ],
        ], ['display_template' => '{{ $hostname }} ({{ $ip }})']);

        $device = Device::where('hostname', 'unchecked.example.com')->firstOrFail();
        $response->assertOk()->assertJson(['status' => 'ok', 'redirect' => route('device', ['device' => $device->device_id])]);
        $this->assertSame('{{ $hostname }} ({{ $ip }})', $device->display_template);

        $snmp = $device->pollingMethod(PollingMethodType::Snmp);
        $this->assertNotNull($snmp);
        $this->assertSame(['transport' => 'udp', 'port_association_mode' => 'ifName'], $snmp->settings); // empty is left to the default
        $this->assertSame($default->id, $snmp->secret_id);
        $this->assertNull($snmp->last_check_successful);
    }

    public function testStoreWithoutCheckingOrCredentialsIsRejected(): void
    {
        LibrenmsConfig::set('snmp.default_credentials', []);

        $this->store('no-credentials.example.com', ['snmp' => ['validate' => '0']])
            ->assertStatus(422)
            ->assertJsonPath('message', trans('exceptions.missing_secret', ['method' => 'SNMP']));

        $this->assertDatabaseMissing('devices', ['hostname' => 'no-credentials.example.com']);
    }

    public function testStoreUnreachableReturnsEachCredentialTried(): void
    {
        $this->defaultSecrets('public', 'private');
        $this->snmpBackend()->shouldReceive('get')->andReturn(new RawSnmpResponse('', 'Timeout', 1));

        $response = $this->store('unreachable.example.com', ['snmp' => ['validate' => '1']]);

        $reasons = [
            'SNMP v2c: No reply using credential "Default public"',
            'SNMP v2c: No reply using credential "Default private"',
        ];
        $response->assertStatus(422)
            ->assertJsonPath('status', 'unreachable')
            ->assertJsonPath('error_details', implode("\n", $reasons))
            ->assertJsonPath('errors.hostname.1', $reasons[0])
            ->assertJsonPath('errors.hostname.2', $reasons[1]);
        $this->assertStringContainsString('unreachable.example.com', $response->json('errors.hostname.0'));
        $this->assertDatabaseMissing('devices', ['hostname' => 'unreachable.example.com']);
    }

    public function testStoreWithExistingSecretOnlyTriesThatSecret(): void
    {
        $this->defaultSecrets('global-community');
        $secret = Secret::factory()->create([
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'target-community'],
        ]);

        $tried = [];
        $this->snmpBackend()->shouldReceive('get')->andReturnUsing(function ($target, $oids, $config) use (&$tried) {
            $tried[] = $config->community;

            return new RawSnmpResponse('', 'Timeout', 1);
        });

        $this->store('existing-secret.example.com', [
            'snmp' => ['validate' => '1', 'secret_mode' => 'existing', 'secret_id' => $secret->id],
        ])->assertStatus(422);

        $this->assertSame(['target-community'], $tried);
    }

    public function testForceAddSkipsAllChecks(): void
    {
        $this->defaultSecrets('public');
        $this->snmpBackend()->shouldNotReceive('get');
        $this->fping()->shouldNotReceive('ping');

        $this->store('forced.example.com', [
            'icmp' => ['validate' => '1'],
            'snmp' => ['validate' => '1'],
        ], ['force_add' => 1])->assertOk();

        $this->assertDatabaseHas('devices', ['hostname' => 'forced.example.com']);
    }

    public function testOnlyMethodsWithValidateCheckedAreChecked(): void
    {
        $this->defaultSecrets('public');
        $this->snmpBackend()->shouldNotReceive('get');
        $this->fping()->shouldReceive('ping')->once()->andReturn(FpingResponse::artificialUp('192.0.2.1'));

        $this->store('partial.example.com', [
            'icmp' => ['validate' => '1'],
            'snmp' => ['validate' => '0'],
        ])->assertOk();

        $device = Device::where('hostname', 'partial.example.com')->firstOrFail();
        $this->assertTrue($device->pollingMethod(PollingMethodType::Icmp)?->last_check_successful);
        $this->assertNull($device->pollingMethod(PollingMethodType::Snmp)?->last_check_successful);
    }

    public function testNewSecretRequiresSecretCreatePermission(): void
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->givePermissionTo('device.create');

        $this->actingAs($user)->postJson(route('device.add.store'), [
            'hostname' => 'no-secret-create.example.com',
            'polling_methods' => ['snmp' => [
                'active' => '1',
                'validate' => '0',
                'secret_mode' => 'new',
                'description' => 'Not Allowed',
                'secret_data' => ['version' => 'v2c', 'community' => 'public'],
            ]],
        ])->assertForbidden();

        $this->assertDatabaseMissing('secrets', ['description' => 'Not Allowed']);
        $this->assertDatabaseMissing('devices', ['hostname' => 'no-secret-create.example.com']);
    }

    public function testNewSecretGetsUniqueDefaultDescription(): void
    {
        Secret::factory()->create(['description' => 'SNMP 127.0.0.10', 'secret_type' => SecretType::Snmp]);

        $this->store('127.0.0.10', ['snmp' => [
            'validate' => '0',
            'secret_mode' => 'new',
            'description' => '',
            'secret_data' => ['version' => 'v2c', 'community' => 'y'],
        ]])->assertOk();

        $this->assertDatabaseHas('secrets', ['description' => 'SNMP 127.0.0.10 (2)']);
    }

    public function testStoreRequiresAPollingMethod(): void
    {
        $this->actingAs($this->admin)->postJson(route('device.add.store'), [
            'hostname' => 'no-methods.example.com',
            'polling_methods' => [],
        ])->assertStatus(422)->assertJsonValidationErrors(['polling_methods' => 'At least one polling method is required']);

        $this->actingAs($this->admin)->postJson(route('device.add.store'), [
            'hostname' => 'inactive-methods.example.com',
            'polling_methods' => ['snmp' => ['active' => 0]],
        ])->assertStatus(422)->assertJsonStructure(['errors' => ['polling_methods']]);

        $this->assertDatabaseMissing('devices', ['hostname' => 'inactive-methods.example.com']);
    }

    public function testStoreRejectsLongDisplayTemplate(): void
    {
        $this->store('display-test.example.com', ['snmp' => ['validate' => '0']], ['display_template' => str_repeat('a', 129)])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['display_template']);
    }

    public function testStoreDoesNotLeakUnexpectedExceptionMessages(): void
    {
        $this->fping()->shouldReceive('ping')->andThrow(new \RuntimeException('SQLSTATE[23000] secret details'));

        $response = $this->store('127.0.0.12', ['icmp' => ['validate' => '1']]);

        $response->assertStatus(422);
        $this->assertStringNotContainsString('SQLSTATE', $response->getContent());
    }

    /**
     * @param  array<string, array<string, mixed>>  $methods  method type => form data, all active
     * @param  array<string, mixed>  $extra
     * @return TestResponse<JsonResponse>
     */
    private function store(string $hostname, array $methods, array $extra = []): TestResponse
    {
        return $this->actingAs($this->admin)->postJson(route('device.add.store'), [
            'hostname' => $hostname,
            'poller_group' => 0,
            'polling_methods' => array_map(fn (array $data) => ['active' => '1', 'secret_mode' => 'default', ...$data], $methods),
            ...$extra,
        ]);
    }

    /**
     * Create SNMP v2c default credentials, in order.
     *
     * @return Secret[]
     */
    private function defaultSecrets(string ...$communities): array
    {
        $secrets = array_map(fn (string $community) => Secret::factory()->create([
            'description' => "Default $community",
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => $community],
        ]), $communities);
        LibrenmsConfig::set('snmp.default_credentials', array_map(fn (Secret $secret) => $secret->id, $secrets));

        return $secrets;
    }

    private function snmpBackend(): MockInterface
    {
        $backend = Mockery::mock(SnmpBackendInterface::class);
        $this->app->instance(SnmpBackendInterface::class, $backend);

        return $backend;
    }

    private function fping(): MockInterface
    {
        $fping = Mockery::mock(Fping::class);
        $this->app->instance(Fping::class, $fping);

        return $fping;
    }
}
