<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\AlertTransport;
use App\Models\AlertTransportGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\TestWith;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class AlertTransportControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
    }

    public function testIndexListsTransportsAndGroups(): void
    {
        $transport = AlertTransport::factory()->create(['transport_name' => 'Ops <Mail>']);
        $group = new AlertTransportGroup(['transport_group_name' => 'On Call']);
        $group->save();
        $group->transports()->attach($transport);

        $this->actingAs($this->admin())
            ->get(route('alert-transports.index'))
            ->assertOk()
            ->assertSee('Ops &lt;Mail&gt;', false)
            ->assertSee('On Call')
            ->assertSee('Mail: Ops &lt;Mail&gt;', false);
    }

    public function testIndexShowsGroupMemberTypeNameAndSkipsEmptyDetails(): void
    {
        $transport = AlertTransport::factory()->api(body: '', password: '')->create(['transport_name' => 'Webhook']);
        $group = new AlertTransportGroup(['transport_group_name' => 'Hooks']);
        $group->save();
        $group->transports()->attach($transport);

        $this->actingAs($this->admin())
            ->get(route('alert-transports.index'))
            ->assertOk()
            ->assertSee('API: Webhook')
            ->assertSee('API URL: https://librenms.org')
            ->assertDontSee('Auth Username:')
            ->assertDontSee('Auth Password:');
    }

    public function testIndexEmptyStates(): void
    {
        AlertTransportGroup::query()->delete();
        AlertTransport::query()->delete();

        $this->actingAs($this->admin())
            ->get(route('alert-transports.index'))
            ->assertOk()
            ->assertSee('No alert transports configured')
            ->assertSee('No transport groups configured');
    }

    public function testIndexSelectsTab(): void
    {
        $this->actingAs($this->admin())
            ->get(route('alert-transports.index', ['tab' => 'groups']))
            ->assertOk()
            ->assertSee("activeTab: 'groups'", false);
    }

    public function testIndexRejectsInvalidTab(): void
    {
        $this->actingAs($this->admin())
            ->get(route('alert-transports.index', ['tab' => 'bogus']))
            ->assertSessionHasErrors('tab');
    }

    #[TestWith(['view'])]
    #[TestWith(['update'])]
    public function testShowAllowsViewOrUpdatePermission(string $permission): void
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->givePermissionTo(Permission::findOrCreate('alert-transport.' . $permission));
        $transport = AlertTransport::factory()->create();
        $group = new AlertTransportGroup(['transport_group_name' => 'On Call']);
        $group->save();
        $group->transports()->attach($transport);

        $this->actingAs($user)->getJson(route('alert-transports.show', $transport))
            ->assertOk()
            ->assertJsonPath('id', $transport->transport_id)
            ->assertJsonPath('name', $transport->transport_name);
        $this->getJson(route('alert-transport-groups.show', $group))
            ->assertOk()
            ->assertJsonPath('id', $group->transport_group_id)
            ->assertJsonPath('name', 'On Call')
            ->assertJsonPath('members.0.id', $transport->transport_id);
    }

    public function testShowForbidsUserWithoutViewOrUpdatePermission(): void
    {
        $user = User::factory()->create(['enabled' => 1]);
        $transport = AlertTransport::factory()->create();
        $group = new AlertTransportGroup(['transport_group_name' => 'On Call']);
        $group->save();

        $this->actingAs($user)->getJson(route('alert-transports.show', $transport))->assertForbidden();
        $this->getJson(route('alert-transport-groups.show', $group))->assertForbidden();
    }

    public function testStoreSpecifiedEmailWithCompleteFormPayload(): void
    {
        $this->actingAs($this->admin())
            ->postJson(route('alert-transports.store'), [
                'name' => 'Specified Email',
                'type' => 'mail',
                'is_default' => false,
                'config' => [
                    'mail-contact' => 'email',
                    'email' => 'ops@example.com',
                    'role' => '',
                    'bcc' => false,
                    'attach-graph' => true,
                ],
            ])
            ->assertCreated();

        $transport = AlertTransport::where('transport_name', 'Specified Email')->sole();
        $this->assertSame([
            'mail-contact' => 'email',
            'email' => 'ops@example.com',
            'role' => null,
            'bcc' => null,
            'attach-graph' => 'on',
        ], $transport->transport_config);
    }

    #[TestWith([null])]
    #[TestWith([''])]
    #[TestWith(['nonexistent-alert-role'])]
    public function testStoreRejectsInvalidRoleForRoleContact(?string $role): void
    {
        $this->actingAs($this->admin())
            ->postJson(route('alert-transports.store'), [
                'name' => 'Role Mail',
                'type' => 'mail',
                'config' => ['mail-contact' => 'role', 'role' => $role],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('role');

        $this->assertDatabaseMissing('alert_transports', ['transport_name' => 'Role Mail']);
    }

    public function testStoreRoleContactWithExistingRole(): void
    {
        $this->actingAs($this->admin())
            ->postJson(route('alert-transports.store'), [
                'name' => 'Role Mail',
                'type' => 'mail',
                'config' => ['mail-contact' => 'role', 'role' => 'user'],
            ])
            ->assertCreated();

        $transport = AlertTransport::where('transport_name', 'Role Mail')->sole();
        $this->assertSame('user', $transport->transport_config['role']);
    }

    public function testStoreValidatesTransportConfig(): void
    {
        $this->actingAs($this->admin())
            ->postJson(route('alert-transports.store'), [
                'name' => 'Mail',
                'type' => 'mail',
                'config' => ['mail-contact' => 'email', 'email' => 'not an email'],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseMissing('alert_transports', ['transport_name' => 'Mail']);
    }

    public function testStoreAndUpdateTransport(): void
    {
        $this->actingAs($this->admin())
            ->postJson(route('alert-transports.store'), [
                'name' => 'Mail',
                'type' => 'mail',
                'is_default' => true,
                'config' => ['mail-contact' => 'email', 'email' => 'ops@example.com', 'bcc' => true, 'unknown' => 'dropped'],
            ])
            ->assertCreated();

        $transport = AlertTransport::where('transport_name', 'Mail')->sole();
        $this->assertTrue($transport->is_default);
        $this->assertSame('ops@example.com', $transport->transport_config['email']);
        $this->assertSame('on', $transport->transport_config['bcc']);
        $this->assertArrayNotHasKey('unknown', $transport->transport_config);

        $this->actingAs($this->admin())
            ->getJson(route('alert-transports.show', $transport))
            ->assertOk()
            ->assertJsonPath('type', 'mail')
            ->assertJsonPath('config.email', 'ops@example.com');

        $this->actingAs($this->admin())
            ->putJson(route('alert-transports.update', $transport), [
                'name' => 'Renamed',
                'type' => 'mail',
                'config' => ['mail-contact' => 'email', 'email' => 'new@example.com', 'bcc' => false],
            ])
            ->assertOk();

        $transport->refresh();
        $this->assertSame('Renamed', $transport->transport_name);
        $this->assertFalse($transport->is_default);
        $this->assertNull($transport->transport_config['bcc']);
    }

    public function testGroupCrud(): void
    {
        $transports = AlertTransport::factory()->count(2)->create();

        $this->actingAs($this->admin())
            ->postJson(route('alert-transport-groups.store'), ['name' => 'Group', 'members' => $transports->pluck('transport_id')])
            ->assertCreated();

        $group = AlertTransportGroup::where('transport_group_name', 'Group')->sole();
        $this->assertCount(2, $group->transports);

        $this->actingAs($this->admin())
            ->putJson(route('alert-transport-groups.update', $group), ['name' => 'Group', 'members' => [$transports[0]->transport_id]])
            ->assertOk();
        $this->assertSame([$transports[0]->transport_id], $group->transports()->pluck('alert_transports.transport_id')->all());

        $this->actingAs($this->admin())
            ->postJson(route('alert-transport-groups.store'), ['name' => 'Empty', 'members' => []])
            ->assertUnprocessable();

        $this->actingAs($this->admin())
            ->deleteJson(route('alert-transport-groups.destroy', $group))
            ->assertOk();
        $this->assertModelMissing($group);
    }

    public function testUserCannotModifyTransports(): void
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        $transport = AlertTransport::factory()->create();

        $this->actingAs($user)->postJson(route('alert-transports.store'), ['name' => 'x', 'type' => 'mail'])->assertForbidden();
        $this->actingAs($user)->putJson(route('alert-transports.update', $transport), ['name' => 'x', 'type' => 'mail'])->assertForbidden();
        $this->actingAs($user)->deleteJson(route('alert-transports.destroy', $transport))->assertForbidden();
        $this->actingAs($user)->postJson(route('alert-transport-groups.store'), ['name' => 'x', 'members' => [$transport->transport_id]])->assertForbidden();
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
