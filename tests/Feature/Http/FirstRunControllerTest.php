<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Http\Controllers\FirstRunController;
use App\Models\Device;
use App\Models\Secret;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Enum\SecretType;
use LibreNMS\Scan\LocalNetworks;
use LibreNMS\Tests\DBTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class FirstRunControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        $this->admin = User::factory()->admin()->create(['enabled' => 1]);
        Device::query()->delete();
        LibrenmsConfig::set('first_run_complete', false);
    }

    public function testDashboardRedirectsToFirstRunWithoutDevices(): void
    {
        $this->actingAs($this->admin)->get(route('home'))
            ->assertRedirect(route('first-run'));
    }

    public function testFirstRunIsNotShownOnceSetUp(): void
    {
        $this->assertTrue(FirstRunController::shouldShow($this->admin));

        Device::factory()->create();
        $this->assertFalse(FirstRunController::shouldShow($this->admin), 'there are devices');

        Device::query()->delete();
        LibrenmsConfig::set('first_run_complete', true);
        $this->assertFalse(FirstRunController::shouldShow($this->admin), 'the first run is complete');
    }

    public function testFirstRunIsNotShownToUsersThatCanNotSetUp(): void
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole(Role::findOrCreate('user'));

        $this->assertFalse(FirstRunController::shouldShow($user));
        $this->actingAs($user)->get(route('first-run'))->assertForbidden();
    }

    public function testFirstRunNeedsPermissionToCreateSecrets(): void
    {
        $user = $this->userWith(['settings.update', 'device.create']);

        $this->assertFalse(FirstRunController::shouldShow($user), 'the credentials could not be saved');
        $this->actingAs($user)->get(route('first-run'))->assertForbidden();

        $user->givePermissionTo(Permission::findOrCreate('secret.create'));
        $this->assertTrue(FirstRunController::shouldShow($user->fresh()));
    }

    public function testCommunitiesAreOnlyShownToUsersThatCanUnmaskSecrets(): void
    {
        $secret = Secret::create(['description' => 'Office community', 'secret_type' => SecretType::SnmpCommunity, 'data' => ['community' => 'hidden-community']]);
        LibrenmsConfig::set('snmp.default_credentials', [$secret->id]);
        $user = $this->userWith(['settings.update', 'device.create', 'secret.create']);

        $this->actingAs($user)->get(route('first-run'))
            ->assertOk()
            ->assertDontSee('hidden-community')
            ->assertSee('Office community');

        $user->givePermissionTo(Permission::findOrCreate('secret.unmask'));
        $this->actingAs($user->fresh())->get(route('first-run'))
            ->assertOk()
            ->assertSee('hidden-community');
    }

    public function testIndexShowsTheSavedNetworks(): void
    {
        LibrenmsConfig::set('nets', ['198.51.100.0/24']);

        $this->actingAs($this->admin)->get(route('first-run'))
            ->assertOk()
            ->assertSee('198.51.100.0');
    }

    public function testIndexDoesNotClaimSuggestionsWhenThereAreNone(): void
    {
        LibrenmsConfig::set('nets', []);
        $this->app->instance(LocalNetworks::class, new LocalNetworks(containerMarkers: [__FILE__]));

        $this->actingAs($this->admin)->get(route('first-run'))
            ->assertOk()
            ->assertViewHas('config.networks', [])
            ->assertViewHas('config.networks_suggested', false);
    }

    public function testIndexListsTheDefaultCredentialsInOrder(): void
    {
        $v3 = Secret::create(['description' => 'First v3', 'secret_type' => SecretType::SnmpV3, 'data' => ['authname' => 'first-v3', 'authpass' => 'never-sent']]);
        $community = Secret::create(['description' => 'Second community', 'secret_type' => SecretType::SnmpCommunity, 'data' => ['community' => 'second-community']]);
        LibrenmsConfig::set('snmp.default_credentials', [$v3->id, $community->id]);

        $response = $this->actingAs($this->admin)->get(route('first-run'))->assertOk();

        $this->assertMatchesRegularExpression('/first-v3.*second-community/s', $response->getContent());
        $response->assertDontSee('never-sent');
    }

    public function testCredentialsAreSavedInTheOrderGiven(): void
    {
        $existingCommunity = Secret::create(['description' => 'Existing community', 'secret_type' => SecretType::SnmpCommunity, 'data' => ['community' => 'existing-community']]);
        $kept = Secret::create(['description' => 'Existing v3', 'secret_type' => SecretType::SnmpV3, 'data' => ['authname' => 'existing']]);
        $removed = Secret::create(['description' => 'Removed v3', 'secret_type' => SecretType::SnmpV3, 'data' => ['authname' => 'removed']]);
        LibrenmsConfig::set('snmp.default_credentials', [$removed->id, $kept->id]);

        $response = $this->actingAs($this->admin)->postJson(route('first-run.credentials'), [
            'credentials' => [
                ['type' => 'v3', 'authlevel' => 'authNoPriv', 'authname' => 'monitor', 'authpass' => 'secret-pass', 'authalgo' => 'SHA'],
                ['type' => 'community', 'community' => 'existing-community'],
                ['id' => $kept->id],
                ['type' => 'community', 'community' => 'new-community'],
            ],
        ])->assertOk();

        $ids = LibrenmsConfig::get('snmp.default_credentials');
        $this->assertCount(4, $ids);
        $added = Secret::findOrFail($ids[0]);
        $this->assertSame(SecretType::SnmpV3, $added->secret_type);
        $this->assertSame('monitor', $added->data['authname']);
        $this->assertSame('SHA', $added->data['authalgo']);
        $this->assertSame($existingCommunity->id, $ids[1], 'a community that is already a secret is reused');
        $this->assertSame($kept->id, $ids[2]);
        $this->assertSame('new-community', Secret::findOrFail($ids[3])->data['community']);
        $this->assertNotNull(Secret::find($removed->id), 'a removed default is kept as a secret');

        // the page lists the saved credentials, without SNMPv3 passwords
        $response->assertJsonPath('credentials.0.id', $ids[0])
            ->assertJsonPath('credentials.0.type', 'v3')
            ->assertJsonPath('credentials.1.community', 'existing-community')
            ->assertJsonMissingPath('credentials.0.authpass');
    }

    public function testCredentialsValidatesSnmpV3(): void
    {
        $secrets = Secret::count();

        $this->actingAs($this->admin)->postJson(route('first-run.credentials'), [
            'credentials' => [
                ['type' => 'community', 'community' => 'public'],
                ['type' => 'v3', 'authlevel' => 'authPriv', 'authname' => 'monitor', 'authpass' => 'short'],
            ],
        ])->assertUnprocessable()->assertJsonValidationErrors(['credentials.1.authpass', 'credentials.1.cryptopass']);

        $this->assertSame($secrets, Secret::count(), 'nothing is saved when a credential is invalid');
    }

    public function testCredentialsRequiresACredential(): void
    {
        $this->actingAs($this->admin)->postJson(route('first-run.credentials'), ['credentials' => []])
            ->assertUnprocessable()->assertJsonValidationErrors('credentials');
    }

    public function testNetworksAreSavedToTheNetsSetting(): void
    {
        $this->actingAs($this->admin)->postJson(route('first-run.networks'), ['networks' => ['198.51.100.0/24', '203.0.113.0/25']])
            ->assertOk();

        $this->assertSame(['198.51.100.0/24', '203.0.113.0/25'], LibrenmsConfig::get('nets'));
    }

    public function testNetworksRejectsInvalidAndLargeNetworks(): void
    {
        $this->actingAs($this->admin)->postJson(route('first-run.networks'), ['networks' => ['192.0.2.1', 'not-a-network']])
            ->assertUnprocessable()->assertJsonValidationErrors(['networks.0', 'networks.1']);

        $this->actingAs($this->admin)->postJson(route('first-run.networks'), ['networks' => ['10.0.0.0/16']])
            ->assertUnprocessable()->assertJsonValidationErrors('networks');

        $this->assertSame([], LibrenmsConfig::get('nets'));
    }

    public function testScanNeedsTheNetsSetting(): void
    {
        LibrenmsConfig::set('nets', []);

        $this->actingAs($this->admin)->postJson(route('first-run.scan'))
            ->assertUnprocessable()->assertJsonValidationErrors('networks');
    }

    public function testFinishCompletesTheFirstRun(): void
    {
        $this->actingAs($this->admin)->post(route('first-run.finish'), ['to' => 'devices'])
            ->assertRedirect(route('devices'));

        $this->assertTrue(LibrenmsConfig::get('first_run_complete'));
        $this->actingAs($this->admin)->get(route('home'))->assertOk();
    }

    /**
     * @param  string[]  $permissions
     */
    private function userWith(array $permissions): User
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }
        $user = User::factory()->create(['enabled' => 1]);
        $user->givePermissionTo($permissions);

        return $user;
    }
}
