<?php

namespace LibreNMS\Tests\Feature;

use App\Models\Device;
use App\Models\Port;
use App\Models\PortsFdb;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;

final class FdbTablesTest extends DBTestCase
{
    use DatabaseTransactions;

    private Device $device;

    protected function setUp(): void
    {
        parent::setUp();

        $this->device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $this->device->device_id]);
        foreach (['02aaaaaaaa01' => 100, '02aaaaaaaa02' => 200, '02aaaaaaaa03' => 0] as $mac => $vlan) {
            PortsFdb::factory()->create(['device_id' => $this->device->device_id, 'port_id' => $port->port_id, 'mac_address' => $mac, 'vlan' => $vlan]);
        }
    }

    public function testShowsTheVlanNumberAndNothingWithoutOne(): void
    {
        $vlans = collect($this->rows())->pluck('vlan', 'mac_address');

        $this->assertSame(100, $vlans['02:aa:aa:aa:aa:01']);
        $this->assertSame('', $vlans['02:aa:aa:aa:aa:03']);
    }

    public function testSearchByVlan(): void
    {
        $this->assertSame(['02:aa:aa:aa:aa:02'], $this->macs(['searchby' => 'vlan', 'searchPhrase' => '200']));
    }

    public function testSearchAllFieldsMatchesVlan(): void
    {
        $this->assertSame(['02:aa:aa:aa:aa:01'], $this->macs(['searchPhrase' => '100']));
    }

    public function testTextSearchDoesNotMatchEntriesWithoutAVlan(): void
    {
        $this->assertSame([], $this->macs(['searchPhrase' => 'not-a-vlan']));
    }

    public function testSortByVlan(): void
    {
        $this->assertSame([200, 100, ''], collect($this->rows(['sort' => ['vlan' => 'desc']]))->pluck('vlan')->all());
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<string>
     */
    private function macs(array $params): array
    {
        return collect($this->rows($params))->pluck('mac_address')->all();
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<array<string, mixed>>
     */
    private function rows(array $params = []): array
    {
        return $this->actingAs(User::factory()->admin()->create(['enabled' => true]))
            ->postJson(url('/ajax/table/fdb-tables'), $params + ['device_id' => $this->device->device_id])
            ->assertOk()
            ->json('rows');
    }
}
