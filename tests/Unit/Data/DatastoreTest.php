<?php

/**
 * DatastoreTest.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit\Data;

use App\Facades\LibrenmsConfig;
use LibreNMS\Data\Store\Datastore;
use LibreNMS\Interfaces\Data\Datastore as DatastoreContract;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('datastores')]
final class DatastoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        LibrenmsConfig::forget([
            'graphite',
            'influxdb',
            'influxdbv2',
            'kafka',
            'opentsdb',
            'prometheus',
            'rrd',
        ]);
    }

    public function testDefaultInitialization(): void
    {
        $ds = $this->app->make('Datastore');
        $stores = $ds->getStores();
        $this->assertCount(1, $stores, 'Incorrect number of default stores enabled');

        $this->assertEquals(\LibreNMS\Data\Store\Rrd::class, $stores[0]::class, 'The default enabled store should be Rrd');
    }

    public function testInitialization(): void
    {
        LibrenmsConfig::set('rrd.enable', false);
        LibrenmsConfig::set('graphite.enable', true);
        LibrenmsConfig::set('influxdb.enable', true);
        LibrenmsConfig::set('influxdbv2.enable', true);
        LibrenmsConfig::set('opentsdb.enable', true);
        LibrenmsConfig::set('prometheus.enable', true);
        LibrenmsConfig::set('kafka.enable', false);

        $ds = $this->app->make('Datastore');
        $stores = $ds->getStores();
        $this->assertCount(5, $stores, 'Incorrect number of default stores enabled');

        $enabled = array_map(get_class(...), $stores);

        $expected_enabled = [
            \LibreNMS\Data\Store\Graphite::class,
            \LibreNMS\Data\Store\InfluxDB::class,
            \LibreNMS\Data\Store\InfluxDBv2::class,
            \LibreNMS\Data\Store\OpenTSDB::class,
            \LibreNMS\Data\Store\Prometheus::class,
        ];

        $this->assertEquals($expected_enabled, $enabled, 'Expected all non-default stores to be initialized');
    }

    public function testTimestampDefault(): void
    {
        $store = \Mockery::mock(DatastoreContract::class);
        $ds = new Datastore([$store]);

        $store->shouldReceive('write')->with('none', ['f' => 1], [], [])->once();
        $ds->write('none', ['f' => 1]);

        $ds->setTimestamp(1700000000);
        $store->shouldReceive('write')->with('default', ['f' => 1], [], ['timestamp' => 1700000000])->once();
        $ds->write('default', ['f' => 1]);

        $store->shouldReceive('write')->with('explicit', ['f' => 1], [], ['timestamp' => 1600000000])->once();
        $ds->write('explicit', ['f' => 1], [], ['timestamp' => 1600000000]);

        $ds->setTimestamp(null);
        $store->shouldReceive('write')->with('cleared', ['f' => 1], [], [])->once();
        $ds->write('cleared', ['f' => 1]);
    }
}
