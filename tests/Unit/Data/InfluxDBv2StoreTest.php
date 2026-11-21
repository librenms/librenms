<?php

/**
 * InfluxDBv2StoreTest.php
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

namespace LibreNMS\Tests\Unit\Data;

use App\Models\Device;
use InfluxDB2\Point;
use InfluxDB2\WriteApi;
use LibreNMS\Data\Store\InfluxDBv2;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('datastores')]
final class InfluxDBv2StoreTest extends TestCase
{
    public function testZeroTagValuesAreKeptAndEmptyTagValuesAreBlank(): void
    {
        $writeApi = \Mockery::mock(WriteApi::class);
        $writeApi->shouldReceive('close');
        $influx = new InfluxDBv2;
        new \ReflectionProperty($influx, 'client')->setValue($influx, $writeApi);

        $tags = ['ifIndex' => 0, 'sensor_index' => '0', 'ifAlias' => null, 'ifName' => ''];

        $writeApi->shouldReceive('write')
            ->with(\Mockery::on(fn ($point) => $point instanceof Point
                && str_starts_with((string) $point->toLineProtocol(), 'testmeasure,hostname=testhost,ifAlias=_blank_,ifIndex=0,ifName=_blank_,sensor_index=0 value=1')))
            ->once();
        $influx->write('testmeasure', ['value' => 1], $tags, ['device' => new Device(['hostname' => 'testhost'])]); // @phpstan-ignore argument.type (null tags are outside the contract but still sent by callers)
    }
}
