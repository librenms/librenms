<?php
/*
 * LocationTest.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit;

use App\Models\Device;
use App\Models\Location;
use LibreNMS\Config;
use LibreNMS\Interfaces\Geocoder;
use LibreNMS\Tests\TestCase;
use LibreNMS\Util\Dns;
use Mockery\MockInterface;

class LocationTest extends TestCase
{
    public function testCanSetLocation()
    {
        $device = Device::factory()->make(); /** @var Device $device */
        $device->setLocation('Where');

        $this->assertEquals($device->location->location, 'Where');
        $this->assertNull($device->location->lat);
        $this->assertNull($device->location->lng);

        $device->setLocation(null);
        $this->assertNull($device->location);
    }

    public function testCanNotSetLocation()
    {
        $device = Device::factory()->make(); /** @var Device $device */
        $location = Location::factory()->make();

        $device->override_sysLocation = true;
        $device->setLocation($location->location);
        $this->assertNull($device->location);
    }

    public function testCanSetEncodedLocation()
    {
        Config::set('geoloc.dns', false);
        $device = Device::factory()->make(); /** @var Device $device */

        // valid coords
        $location = Location::factory()->withCoordinates()->make();
        $device->setLocation("$location->location [$location->lat,$location->lng]", true);
        $this->assertEquals("$location->location [$location->lat,$location->lng]", $device->location->location);
        $this->assertEquals($location->location, $device->location->display());
        $this->assertEquals($location->lat, $device->location->lat);
        $this->assertEquals($location->lng, $device->location->lng);

        // with space
        $location = Location::factory()->withCoordinates()->make();
        $device->setLocation("$location->location [$location->lat, $location->lng]", true);
        $this->assertEquals("$location->location [$location->lat, $location->lng]", $device->location->location);
        $this->assertEquals($location->location, $device->location->display());
        $this->assertEquals("$location->location [$location->lat,$location->lng]", $device->location->display(true));
        $this->assertEquals($location->lat, $device->location->lat);
        $this->assertEquals($location->lng, $device->location->lng);

        // invalid coords
        $location = Location::factory()->withCoordinates()->make(['lat' => 251.5007138]);
        $name = "$location->location [$location->lat,$location->lng]";
        $device->setLocation($name, true);
        $this->assertEquals($name, $device->location->location);
        $this->assertEquals($name, $device->location->display());
        $this->assertEquals($name, $device->location->display(true));
        $this->assertNull($device->location->lat);
        $this->assertNull($device->location->lng);
    }

    public function testCanHandleGivenCoordinates()
    {
        Config::set('geoloc.dns', false);
        $device = Device::factory()->make(); /** @var Device $device */
        $location = Location::factory()->withCoordinates()->make();

        $device->setLocation($location);
        $this->assertEquals($location->location, $device->location->location);
        $this->assertEquals($location->location, $device->location->display());
        $this->assertEquals("$location->location [$location->lat,$location->lng]", $device->location->display(true));
        $this->assertEquals($location->lat, $device->location->lat);
        $this->assertEquals($location->lng, $device->location->lng);
    }

    public function testCanNotSetFixedCoordinates()
    {
        $device = Device::factory()->make(); /** @var Device $device */
        $locationOne = Location::factory()->withCoordinates()->make();
        $locationTwo = Location::factory(['location' => $locationOne->location])->withCoordinates()->make();

        $device->setLocation($locationOne);
        $this->assertEquals($locationOne->lat, $device->location->lat);
        $this->assertEquals($locationOne->lng, $device->location->lng);

        $device->location->fixed_coordinates = true;
        $device->setLocation($locationTwo);
        $this->assertEquals($locationOne->lat, $device->location->lat);
        $this->assertEquals($locationOne->lng, $device->location->lng);

        $device->location->fixed_coordinates = false;
        $device->setLocation($locationTwo);
        $this->assertEquals($locationTwo->lat, $device->location->lat);
        $this->assertEquals($locationTwo->lng, $device->location->lng);
    }

    public function testDnsLookup()
    {
        $example = 'SW1A2AA.find.me.uk';
        $expected = ['lat' => 51.50354111111111, 'lng' => -0.12766972222222223];

        $result = (new Dns())->getCoordinates($example);

        $this->assertEquals($expected, $result);
    }

    public function testCanSetDnsCoordinate()
    {
        Config::set('geoloc.dns', true);
        $device = Device::factory()->make(); /** @var Device $device */
        $location = Location::factory()->withCoordinates()->make();

        $this->mock(Dns::class, function (MockInterface $mock) use ($location) {
            $mock->shouldReceive('getCoordinates')->once()->andReturn($location->only(['lat', 'lng']));
        });

        $device->setLocation($location->location, true);
        $this->assertEquals($location->location, $device->location->location);
        $this->assertEquals($location->lat, $device->location->lat);
        $this->assertEquals($location->lng, $device->location->lng);

        Config::set('geoloc.dns', false);
        $device->setLocation('No DNS', true);
        $this->assertEquals('No DNS', $device->location->location);
        $this->assertNull($device->location->lat);
        $this->assertNull($device->location->lng);
    }

    public function testCanSetByApi()
    {
        $device = Device::factory()->make(); /** @var Device $device */
        $location = Location::factory()->withCoordinates()->make();

        $this->mock(Geocoder::class, function (MockInterface $mock) use ($location) {
            $mock->shouldReceive('getCoordinates')->once()->andReturn($location->only(['lat', 'lng']));
        });

        Config::set('geoloc.latlng', false);
        $device->setLocation('No API', true);
        $this->assertEquals('No API', $device->location->location);
        $this->assertNull($device->location->lat);
        $this->assertNull($device->location->lng);

        Config::set('geoloc.latlng', true);
        $device->setLocation('API', true);
        $this->assertEquals('API', $device->location->location);
        $this->assertEquals($location->lat, $device->location->lat);
        $this->assertEquals($location->lng, $device->location->lng);

        // preset coord = skip api
        $device->setLocation('API', true);
        $this->assertEquals($location->lat, $device->location->lat);
        $this->assertEquals($location->lng, $device->location->lng);
    }

    public function testCorrectPrecedence()
    {
        $device = Device::factory()->make(); /** @var Device $device */
        $location_encoded = Location::factory()->withCoordinates()->make();
        $location_fixed = Location::factory()->withCoordinates()->make();
        $location_api = Location::factory()->withCoordinates()->make();
        $location_dns = Location::factory()->withCoordinates()->make();

        Config::set('geoloc.dns', true);
        $this->mock(Dns::class, function (MockInterface $mock) use ($location_dns) {
            $mock->shouldReceive('getCoordinates')->times(3)->andReturn(
                $location_dns->only(['lat', 'lng']),
                [],
                []
            );
        });

        Config::set('geoloc.latlng', true);
        $this->mock(Geocoder::class, function (MockInterface $mock) use ($location_api) {
            $mock->shouldReceive('getCoordinates')->once()->andReturn($location_api->only(['lat', 'lng']));
        });

        // fixed first
        $location_fixed->location = "$location_fixed [-42, 42]"; // encoded should not be used
        $device->setLocation($location_fixed, true);
        $this->assertEquals($location_fixed->lat, $device->location->lat);
        $this->assertEquals($location_fixed->lng, $device->location->lng);

        // then encoded
        $device->setLocation($location_encoded->display(true), true);
        $this->assertEquals($location_encoded->lat, $device->location->lat);
        $this->assertEquals($location_encoded->lng, $device->location->lng);

        // then dns
        $device->setLocation($location_encoded->location, true);
        $this->assertEquals($location_dns->lat, $device->location->lat);
        $this->assertEquals($location_dns->lng, $device->location->lng);

        // then api
        $device->setLocation($location_encoded->location, true);
        $this->assertEquals($location_dns->lat, $device->location->lat);
        $this->assertEquals($location_dns->lng, $device->location->lng);

        $device->location->lat = null; // won't be used if latitude is set
        $device->setLocation($location_encoded->location, true);
        $this->assertEquals($location_api->lat, $device->location->lat);
        $this->assertEquals($location_api->lng, $device->location->lng);
    }
}
