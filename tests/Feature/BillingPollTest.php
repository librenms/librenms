<?php

/**
 * BillingPollTest.php
 *
 * Bill polling accounts the counters stored by the port and mpls pollers.
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
use LibreNMS\Billing;
use LibreNMS\Tests\TestCase;

class BillingPollTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        LibrenmsConfig::set('distributed_poller', false);
    }

    public function testPortAndSapCountersAreAccounted(): void
    {
        $device = Device::factory()->create(['status' => 1, 'last_polled' => now()->subMinutes(5)]);
        $port = Port::factory()->create([
            'device_id' => $device->device_id,
            'ifOperStatus' => 'up',
            'ifSpeed' => 1000000000,
            'ifInOctets' => 1000,
            'ifOutOctets' => 2000,
            'poll_time' => now()->subMinutes(5)->getTimestamp(),
        ]);
        $sap = MplsSap::factory()->up()->create([
            'device_id' => $device->device_id,
            'sapIngressOctets' => 500,
            'sapEgressOctets' => 700,
        ]);

        $bill = Bill::factory()->create();
        $bill->ports()->attach($port->port_id);
        $bill->sources(MplsSap::class)->attach($sap->sap_id);

        // first run only seeds the counters
        Billing::pollBill($bill);
        $this->assertDatabaseHas('bill_counters', ['bill_id' => $bill->bill_id, 'source_type' => 'interface', 'source_id' => $port->port_id, 'in_counter' => 1000, 'in_delta' => 0]);
        $this->assertDatabaseHas('bill_counters', ['bill_id' => $bill->bill_id, 'source_type' => 'mpls_sap', 'source_id' => $sap->sap_id, 'in_counter' => 500, 'in_delta' => 0]);
        $this->assertEquals(0, $bill->data()->latest('timestamp')->first()->delta);

        // the pollers stored new samples
        $port->update(['ifInOctets' => 1100, 'ifOutOctets' => 2300, 'poll_time' => now()->getTimestamp()]);
        $sap->update(['sapIngressOctets' => 510, 'sapEgressOctets' => 720]);
        $device->forceFill(['last_polled' => now()])->save();

        Billing::pollBill($bill);
        $this->assertDatabaseHas('bill_counters', ['source_type' => 'interface', 'source_id' => $port->port_id, 'in_counter' => 1100, 'in_delta' => 100, 'out_delta' => 300]);
        $this->assertDatabaseHas('bill_counters', ['source_type' => 'mpls_sap', 'source_id' => $sap->sap_id, 'in_counter' => 510, 'in_delta' => 10, 'out_delta' => 20]);

        $data = $bill->data()->latest('timestamp')->first();
        $this->assertEquals(110, $data->in_delta);
        $this->assertEquals(320, $data->out_delta);
        $this->assertEquals(430, $data->delta);
        $this->assertEquals(2, $bill->data()->count());
    }

    public function testCountersNotPolledSinceLastRunAreSkipped(): void
    {
        $device = Device::factory()->create(['status' => 1]);
        $port = Port::factory()->create([
            'device_id' => $device->device_id,
            'ifOperStatus' => 'up',
            'ifInOctets' => 1000,
            'ifOutOctets' => 1000,
            'poll_time' => now()->subMinutes(5)->getTimestamp(),
        ]);
        $bill = Bill::factory()->create();
        $bill->ports()->attach($port->port_id);

        Billing::pollBill($bill);
        $port->update(['ifInOctets' => 1500, 'ifOutOctets' => 1500, 'poll_time' => now()->getTimestamp()]);
        Billing::pollBill($bill);
        Billing::pollBill($bill); // the port poller did not run in between

        $this->assertDatabaseHas('bill_counters', ['source_id' => $port->port_id, 'in_counter' => 1500, 'in_delta' => 500]);
        $this->assertEquals(500, $bill->data()->sum('in_delta'));
    }

    public function testCounterTimeIsStoredAsLocalTime(): void
    {
        $timezone = date_default_timezone_get();
        date_default_timezone_set('Europe/Amsterdam');

        try {
            $device = Device::factory()->create(['status' => 1]);
            $poll_time = now()->subMinutes(3)->getTimestamp();
            $port = Port::factory()->create(['device_id' => $device->device_id, 'ifOperStatus' => 'up', 'ifInOctets' => 1, 'ifOutOctets' => 1, 'poll_time' => $poll_time]);
            $bill = Bill::factory()->create();
            $bill->ports()->attach($port->port_id);

            Billing::pollBill($bill);

            $this->assertEquals($poll_time, $bill->ports()->first()->pivot->timestamp->getTimestamp());
        } finally {
            date_default_timezone_set($timezone);
        }
    }

    public function testCounterWrapRepeatsPreviousDelta(): void
    {
        $device = Device::factory()->create(['status' => 1]);
        $port = Port::factory()->create([
            'device_id' => $device->device_id,
            'ifOperStatus' => 'up',
            'ifSpeed' => 0,
            'ifInOctets' => 1000,
            'ifOutOctets' => 1000,
            'poll_time' => now()->subMinutes(10)->getTimestamp(),
        ]);
        $bill = Bill::factory()->create();
        $bill->ports()->attach($port->port_id);

        Billing::pollBill($bill);
        $port->update(['ifInOctets' => 1250, 'ifOutOctets' => 1250, 'poll_time' => now()->subMinutes(5)->getTimestamp()]);
        Billing::pollBill($bill);
        $port->update(['ifInOctets' => 10, 'ifOutOctets' => 10, 'poll_time' => now()->getTimestamp()]); // wrapped
        Billing::pollBill($bill);

        $this->assertDatabaseHas('bill_counters', ['source_id' => $port->port_id, 'in_counter' => 10, 'in_delta' => 250, 'out_delta' => 250]);
    }

    public function testDownSourcesAndUnpolledCountersAreSkipped(): void
    {
        $device = Device::factory()->create(['status' => 1]);
        $down = Port::factory()->create(['device_id' => $device->device_id, 'ifOperStatus' => 'down', 'ifInOctets' => 5, 'ifOutOctets' => 5]);
        $fresh = Port::factory()->create(['device_id' => $device->device_id, 'ifOperStatus' => 'up', 'ifInOctets' => null, 'ifOutOctets' => null]);
        $bill = Bill::factory()->create();
        $bill->ports()->attach([$down->port_id, $fresh->port_id]);

        Billing::pollBill($bill);

        $this->assertDatabaseHas('bill_counters', ['source_id' => $down->port_id, 'in_counter' => null]);
        $this->assertDatabaseHas('bill_counters', ['source_id' => $fresh->port_id, 'in_counter' => null]);
        $this->assertEquals(1, $bill->data()->count()); // the bill has sources, so a (zero) entry is written
    }

    public function testBillWithoutSourcesGetsNoData(): void
    {
        $bill = Bill::factory()->create();

        Billing::pollBill($bill);

        $this->assertEquals(0, $bill->data()->count());
    }
}
