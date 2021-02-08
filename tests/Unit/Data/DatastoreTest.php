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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit\Data;

use LibreNMS\Config;
use LibreNMS\Tests\TestCase;

/**
 * @group datastores
 */
class DatastoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::forget([
            'graphite',
            'influxdb',
            'opentsdb',
            'prometheus',
            'rrd',
        ]);
    }

    public function testDefaultInitialization()
    {
        $ds = $this->app->make('Datastore');
        $stores = $ds->getStores();
        $this->assertCount(1, $stores, 'Incorrect number of default stores enabled');

        $this->assertEquals('LibreNMS\Data\Store\Rrd', get_class($stores[0]), 'The default enabled store should be Rrd');
    }

    public function testInitialization()
    {
        Config::set('rrd.enable', false);
        Config::set('graphite.enable', true);
        Config::set('influxdb.enable', true);
        Config::set('opentsdb.enable', true);
        Config::set('prometheus.enable', true);

        $ds = $this->app->make('Datastore');
        $stores = $ds->getStores();
        $this->assertCount(4, $stores, 'Incorrect number of default stores enabled');

        $enabled = array_map('get_class', $stores);

        $expected_enabled = [
            'LibreNMS\Data\Store\Graphite',
            'LibreNMS\Data\Store\InfluxDB',
            'LibreNMS\Data\Store\OpenTSDB',
            'LibreNMS\Data\Store\Prometheus',
        ];

        $this->assertEquals($expected_enabled, $enabled, 'Expected all non-default stores to be initialized');
    }
}
