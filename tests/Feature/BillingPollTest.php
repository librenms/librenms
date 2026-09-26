<?php

/**
 * BillingPollTest.php
 *
 * Bill polling reads the counters of every source from the device and accounts the deltas.
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
 */

namespace LibreNMS\Tests\Feature;

use App\Facades\LibrenmsConfig;
use App\Models\Bill;
use App\Models\Device;
use App\Models\MplsSap;
use App\Models\Port;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use LibreNMS\Billing;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Tests\TestCase;
use SnmpQuery;

class BillingPollTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, string> what the fake devices answer, by oid */
    private array $snmp = [];

    protected function setUp(): void
    {
        parent::setUp();
        LibrenmsConfig::set('distributed_poller', false);

        SnmpQuery::partialMock()->shouldReceive('get')->andReturnUsing(
            fn ($oids) => new SnmpResponse(Arr::only($this->snmp, Arr::wrap($oids)))
        );
    }

    public function testPortAndSapCountersAreAccounted(): void
    {
        $device = Device::factory()->create(['status' => 1]);
        $port = Port::factory()->create(['device_id' => $device->device_id, 'ifOperStatus' => 'up', 'ifSpeed' => 1000000000]);
        $sap = MplsSap::factory()->up()->create(['device_id' => $device->device_id]);

        $bill = Bill::factory()->create();
        $bill->ports()->attach($port->port_id);
        $bill->sources(MplsSap::class)->attach($sap->sap_id);

        // first run only seeds the counters
        $this->portCounters($port, 1000, 2000);
        $this->sapCounters($sap, [300, 200], [400, 300]);
        Billing::pollBill($bill);
        $this->assertDatabaseHas('bill_counters', ['bill_id' => $bill->bill_id, 'source_type' => 'interface', 'source_id' => $port->port_id, 'in_counter' => 1000, 'in_delta' => 0]);
        $this->assertDatabaseHas('bill_counters', ['bill_id' => $bill->bill_id, 'source_type' => 'mpls_sap', 'source_id' => $sap->sap_id, 'in_counter' => 500, 'out_counter' => 700, 'in_delta' => 0]);
        $this->assertEquals(0, $bill->data()->latest('timestamp')->first()->delta);

        $this->travel(5)->minutes();
        $this->portCounters($port, 1100, 2300);
        $this->sapCounters($sap, [305, 205], [410, 310]);
        Billing::pollBill($bill);
        $this->assertDatabaseHas('bill_counters', ['source_type' => 'interface', 'source_id' => $port->port_id, 'in_counter' => 1100, 'in_delta' => 100, 'out_delta' => 300]);
        $this->assertDatabaseHas('bill_counters', ['source_type' => 'mpls_sap', 'source_id' => $sap->sap_id, 'in_counter' => 510, 'in_delta' => 10, 'out_delta' => 20]);

        $data = $bill->data()->latest('timestamp')->first();
        $this->assertEquals(110, $data->in_delta);
        $this->assertEquals(320, $data->out_delta);
        $this->assertEquals(430, $data->delta);
        $this->assertEquals(2, $bill->data()->count());
    }

    public function testPortFallsBackTo32BitCounters(): void
    {
        $device = Device::factory()->create(['status' => 1]);
        $port = Port::factory()->create(['device_id' => $device->device_id, 'ifOperStatus' => 'up']);
        $bill = Bill::factory()->create();
        $bill->ports()->attach($port->port_id);

        $this->snmp = ["IF-MIB::ifInOctets.$port->ifIndex" => '42', "IF-MIB::ifOutOctets.$port->ifIndex" => '43'];
        Billing::pollBill($bill);

        $this->assertDatabaseHas('bill_counters', ['source_id' => $port->port_id, 'in_counter' => 42, 'out_counter' => 43]);
    }

    public function testCounterWrapRepeatsPreviousDelta(): void
    {
        $device = Device::factory()->create(['status' => 1]);
        $port = Port::factory()->create(['device_id' => $device->device_id, 'ifOperStatus' => 'up', 'ifSpeed' => 0]);
        $bill = Bill::factory()->create();
        $bill->ports()->attach($port->port_id);

        $this->portCounters($port, 1000, 1000);
        Billing::pollBill($bill);
        $this->travel(5)->minutes();
        $this->portCounters($port, 1250, 1250);
        Billing::pollBill($bill);
        $this->travel(5)->minutes();
        $this->portCounters($port, 10, 10); // wrapped
        Billing::pollBill($bill);

        $this->assertDatabaseHas('bill_counters', ['source_id' => $port->port_id, 'in_counter' => 10, 'in_delta' => 250, 'out_delta' => 250]);
    }

    public function testDownAndUnreadableSourcesAreSkipped(): void
    {
        $device = Device::factory()->create(['status' => 1]);
        $down = Port::factory()->create(['device_id' => $device->device_id, 'ifOperStatus' => 'down']);
        $unreadable = Port::factory()->create(['device_id' => $device->device_id, 'ifOperStatus' => 'up']);
        $partial = MplsSap::factory()->up()->create(['device_id' => $device->device_id]);
        $bill = Bill::factory()->create();
        $bill->ports()->attach([$down->port_id, $unreadable->port_id]);
        $bill->sources(MplsSap::class)->attach($partial->sap_id);

        $this->portCounters($down, 5, 5);
        $this->sapCounters($partial, [300, 200], [400, 300]);
        unset($this->snmp[$this->sapOid($partial, 'sapBaseStatsEgressQchipForwardedOutProfOctets')]); // one of the four failed

        Billing::pollBill($bill);

        $this->assertDatabaseHas('bill_counters', ['source_id' => $down->port_id, 'in_counter' => null]);
        $this->assertDatabaseHas('bill_counters', ['source_id' => $unreadable->port_id, 'in_counter' => null]);
        $this->assertDatabaseHas('bill_counters', ['source_id' => $partial->sap_id, 'in_counter' => null]);
        $this->assertEquals(1, $bill->data()->count()); // the bill has sources, so a (zero) entry is written
    }

    public function testSapStatNotKeptIsIgnored(): void
    {
        $device = Device::factory()->create(['status' => 1]);
        $sap = MplsSap::factory()->up()->create(['device_id' => $device->device_id]);
        $bill = Bill::factory()->create();
        $bill->sources(MplsSap::class)->attach($sap->sap_id);

        $this->sapCounters($sap, ['18446744073709551615', 200], [400, 300]);
        Billing::pollBill($bill);

        $this->assertDatabaseHas('bill_counters', ['source_id' => $sap->sap_id, 'in_counter' => 200, 'out_counter' => 700]);
    }

    public function testBillWithoutSourcesGetsNoData(): void
    {
        $bill = Bill::factory()->create();

        Billing::pollBill($bill);

        $this->assertEquals(0, $bill->data()->count());
    }

    private function portCounters(Port $port, int $in, int $out): void
    {
        $this->snmp["IF-MIB::ifHCInOctets.$port->ifIndex"] = (string) $in;
        $this->snmp["IF-MIB::ifHCOutOctets.$port->ifIndex"] = (string) $out;
    }

    /**
     * @param  array{0: int|string, 1: int|string}  $in  offered hi and lo priority octets
     * @param  array{0: int|string, 1: int|string}  $out  forwarded in and out of profile octets
     */
    private function sapCounters(MplsSap $sap, array $in, array $out): void
    {
        $this->snmp[$this->sapOid($sap, 'sapBaseStatsIngressPchipOfferedHiPrioOctets')] = (string) $in[0];
        $this->snmp[$this->sapOid($sap, 'sapBaseStatsIngressPchipOfferedLoPrioOctets')] = (string) $in[1];
        $this->snmp[$this->sapOid($sap, 'sapBaseStatsEgressQchipForwardedInProfOctets')] = (string) $out[0];
        $this->snmp[$this->sapOid($sap, 'sapBaseStatsEgressQchipForwardedOutProfOctets')] = (string) $out[1];
    }

    private function sapOid(MplsSap $sap, string $object): string
    {
        return "TIMETRA-SAP-MIB::$object.$sap->svc_oid.$sap->sapPortId.$sap->sapEncapValue";
    }
}
