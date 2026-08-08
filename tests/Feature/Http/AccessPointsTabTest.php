<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\AccessPoint;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class AccessPointsTabTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
    }

    public function testAuthorizedUserCanRenderAccessPointsTab(): void
    {
        $device = Device::factory()->create();
        AccessPoint::factory()->for($device)->create();

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'accesspoints']))
            ->assertOk()
            ->assertSee('id="access-points"', false)
            ->assertSee(route('table.access-points'), false);
    }

    public function testEndpointReturnsOnlyRequestedDeviceAndExcludesDeletedRadios(): void
    {
        $device = Device::factory()->create();
        $otherDevice = Device::factory()->create();
        AccessPoint::factory()->for($device)->create(['name' => 'Visible AP']);
        AccessPoint::factory()->for($device)->create(['name' => 'Deleted AP', 'deleted' => true]);
        AccessPoint::factory()->for($otherDevice)->create(['name' => 'Other Device AP']);

        $response = $this->actingAs($this->admin())->postJson(route('table.access-points'), $this->tableRequest($device));

        $response->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonCount(1, 'rows');
        $this->assertStringContainsString('Visible AP', $response->json('rows.0.name'));
        $this->assertStringNotContainsString('Deleted AP', $response->getContent());
        $this->assertStringNotContainsString('Other Device AP', $response->getContent());
    }

    public function testEndpointForbidsADeviceTheUserCannotAccess(): void
    {
        $allowedDevice = Device::factory()->create();
        $deniedDevice = Device::factory()->create();
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        $user->devicesOwned()->attach($allowedDevice);

        $this->actingAs($user)
            ->postJson(route('table.access-points'), $this->tableRequest($deniedDevice))
            ->assertForbidden();
    }

    public function testSearchMatchesNameAndMacAddress(): void
    {
        $device = Device::factory()->create();
        AccessPoint::factory()->for($device)->create([
            'name' => 'Conference-West',
            'mac_addr' => 'aa:bb:cc:11:22:33',
        ]);
        AccessPoint::factory()->for($device)->create([
            'name' => 'Lobby-East',
            'mac_addr' => '44:55:66:77:88:99',
        ]);

        $nameResponse = $this->actingAs($this->admin())->postJson(route('table.access-points'), [
            ...$this->tableRequest($device),
            'searchPhrase' => 'ference-We',
        ]);
        $macResponse = $this->postJson(route('table.access-points'), [
            ...$this->tableRequest($device),
            'searchPhrase' => '55:66:77',
        ]);

        $nameResponse->assertOk()->assertJsonPath('total', 1);
        $this->assertStringContainsString('Conference-West', $nameResponse->json('rows.0.name'));
        $macResponse->assertOk()->assertJsonPath('total', 1);
        $this->assertStringContainsString('Lobby-East', $macResponse->json('rows.0.name'));
    }

    public function testClientSortingIsNumeric(): void
    {
        $device = Device::factory()->create();
        AccessPoint::factory()->for($device)->create(['name' => 'Two', 'numasoclients' => 2]);
        AccessPoint::factory()->for($device)->create(['name' => 'Ten', 'numasoclients' => 10]);
        AccessPoint::factory()->for($device)->create(['name' => 'Hundred', 'numasoclients' => 100]);

        $response = $this->actingAs($this->admin())->postJson(route('table.access-points'), [
            ...$this->tableRequest($device),
            'sort' => ['numasoclients' => 'desc'],
        ]);

        $response->assertOk();
        $this->assertSame([100, 10, 2], array_column($response->json('rows'), 'numasoclients'));
    }

    public function testDefaultOrderingUsesNameThenRadioNumber(): void
    {
        $device = Device::factory()->create();
        $bravo = AccessPoint::factory()->for($device)->create(['name' => 'Bravo', 'radio_number' => 0]);
        $alphaTwo = AccessPoint::factory()->for($device)->create(['name' => 'Alpha', 'radio_number' => 2]);
        $alphaZero = AccessPoint::factory()->for($device)->create(['name' => 'Alpha', 'radio_number' => 0]);

        $response = $this->actingAs($this->admin())->postJson(route('table.access-points'), $this->tableRequest($device));

        $response->assertOk();
        $this->assertSame(
            [$alphaZero->accesspoint_id, $alphaTwo->accesspoint_id, $bravo->accesspoint_id],
            array_column($response->json('rows'), 'accesspoint_id')
        );
    }

    public function testEndpointPaginatesInTheQuery(): void
    {
        $device = Device::factory()->create();
        AccessPoint::factory()->for($device)->create(['name' => 'Alpha']);
        AccessPoint::factory()->for($device)->create(['name' => 'Bravo']);
        $charlie = AccessPoint::factory()->for($device)->create(['name' => 'Charlie']);

        $response = $this->actingAs($this->admin())->postJson(route('table.access-points'), [
            ...$this->tableRequest($device),
            'current' => 2,
            'rowCount' => 2,
        ]);

        $response->assertOk()
            ->assertJsonPath('current', 2)
            ->assertJsonPath('rowCount', 1)
            ->assertJsonPath('total', 3)
            ->assertJsonCount(1, 'rows')
            ->assertJsonPath('rows.0.accesspoint_id', $charlie->accesspoint_id);
    }

    public function testSnmpStringsAreEscapedInListAndDetailViews(): void
    {
        $device = Device::factory()->create();
        $accessPoint = AccessPoint::factory()->for($device)->create([
            'name' => '<script>alert("ap")</script>',
            'mac_addr' => '<b>aa:bb</b>',
            'type' => '<em>ax</em>',
            'radio_number' => 0,
        ]);

        $listResponse = $this->actingAs($this->admin())->postJson(route('table.access-points'), $this->tableRequest($device));
        $listResponse->assertOk();
        $this->assertStringContainsString('&lt;script&gt;alert(&quot;ap&quot;)&lt;/script&gt;', $listResponse->json('rows.0.name'));
        $this->assertStringNotContainsString('<script>', $listResponse->json('rows.0.name'));
        $this->assertSame('&lt;em&gt;ax&lt;/em&gt; (0)', $listResponse->json('rows.0.radio'));

        $detailUrl = route('device.accesspoints.show', [
            'device' => $device,
            'accessPoint' => $accessPoint,
        ]);

        $this->assertStringContainsString($detailUrl, $listResponse->json('rows.0.name'));
        $this->assertStringContainsString($detailUrl, $listResponse->json('rows.0.trends'));
        $this->assertStringNotContainsString('ap=', $listResponse->json('rows.0.name'));
        $this->assertStringNotContainsString('ap=', $listResponse->json('rows.0.trends'));

        $this->get($detailUrl)
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(&quot;ap&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("ap")</script>', false)
            ->assertSee('&lt;b&gt;aa:bb&lt;/b&gt;', false)
            ->assertSee('&lt;em&gt;ax&lt;/em&gt;', false);
    }

    public function testModernRouteRendersAccessPointDetail(): void
    {
        $device = Device::factory()->create();
        $accessPoint = AccessPoint::factory()->for($device)->create(['name' => 'Selected AP']);

        $this->actingAs($this->admin())
            ->get(route('device.accesspoints.show', [
                'device' => $device,
                'accessPoint' => $accessPoint,
            ]))
            ->assertOk()
            ->assertSee('Selected AP')
            ->assertSee('All access points')
            ->assertDontSee('id="access-points"', false);
    }

    public function testDetailSelectionCannotCrossDeviceBoundary(): void
    {
        $device = Device::factory()->create();
        $otherDevice = Device::factory()->create();
        AccessPoint::factory()->for($device)->create(['name' => 'Local AP']);
        $foreignAccessPoint = AccessPoint::factory()->for($otherDevice)->create(['name' => 'Foreign Secret AP']);

        $this->actingAs($this->admin())
            ->get(route('device.accesspoints.show', [
                'device' => $device,
                'accessPoint' => $foreignAccessPoint,
            ]))
            ->assertNotFound();
    }

    public function testDeletedAccessPointDetailIsNotFound(): void
    {
        $device = Device::factory()->create();
        $accessPoint = AccessPoint::factory()->for($device)->create(['deleted' => true]);

        $this->actingAs($this->admin())
            ->get(route('device.accesspoints.show', [
                'device' => $device,
                'accessPoint' => $accessPoint,
            ]))
            ->assertNotFound();
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }

    /** @return array<string, mixed> */
    private function tableRequest(Device $device): array
    {
        return [
            'device_id' => $device->device_id,
            'current' => 1,
            'rowCount' => 50,
            'searchPhrase' => '',
        ];
    }
}
