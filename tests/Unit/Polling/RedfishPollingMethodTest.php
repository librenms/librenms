<?php

/**
 * RedfishPollingMethodTest.php
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
 * @copyright  2026 Kevin Zink
 * @author     Kevin Zink <kevin.zink@mpi-hd.mpg.de>
 */

namespace LibreNMS\Tests\Unit\Polling;

use App\Models\Device;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use LibreNMS\Polling\Method\Config\RedfishConfig;
use LibreNMS\Polling\Method\Methods\RedfishPollingMethod;
use LibreNMS\Polling\Method\ProbeResult;
use LibreNMS\Tests\TestCase;

final class RedfishPollingMethodTest extends TestCase
{
    private const string CHASSIS_OUTPUT = <<<'JSON'
{
    "Id": "Chassis",
    "Name": "Chassis Collection",
    "Members": [
        { "@odata.id": "/redfish/v1/Chassis/1" }
    ],
    "MemberIds": [ "1" ]
}
JSON;

    private const string CHASSIS_TWO_OUTPUT = <<<'JSON'
{
    "Id": "Chassis",
    "Name": "Chassis Collection",
    "Members": [
        { "@odata.id": "/redfish/v1/Chassis/1" },
        { "@odata.id": "/redfish/v1/Chassis/2" }
    ],
    "MemberIds": [ "1", "2" ]
}
JSON;

    // some BMCs (e.g. Dell iDRAC) report several chassis without MemberIds, one id can even contain a colon
    private const string CHASSIS_MULTI_OUTPUT = <<<'JSON'
{
    "Id": "Chassis",
    "Name": "Chassis Collection",
    "Members": [
        { "@odata.id": "/redfish/v1/Chassis/System.Embedded.1" },
        { "@odata.id": "/redfish/v1/Chassis/Enclosure.Internal.1-1" },
        { "@odata.id": "/redfish/v1/Chassis/Enclosure.Internal.0-0" },
        { "@odata.id": "/redfish/v1/Chassis/Enclosure.Internal.0-0:RAID.Integrated.1-1" }
    ]
}
JSON;

    private const string THERMAL_OUTPUT = <<<'JSON'
{
    "Id": "Thermal",
    "Temperatures": [
        { "Name": "CPU Temp", "ReadingCelsius": 47 },
        { "Name": "Inlet Temp", "ReadingCelsius": 25.5 }
    ],
    "Fans": [
        { "Name": "Fan 1", "ReadingRPM": 3600 },
        { "Name": "Fan 2", "ReadingRPM": null }
    ]
}
JSON;

    private const string POWER_OUTPUT = <<<'JSON'
{
    "Id": "Power",
    "PowerSupplies": [
        { "Name": "Power Supply 1", "ReadingWatts": 120.5 },
        { "Name": "Power Supply 2", "ReadingWatts": null }
    ]
}
JSON;

    private const string SENSORS_COLLECTION_OUTPUT = <<<'JSON'
{
    "@odata.context": "/redfish/v1/$metadata#SensorCollection.SensorCollection",
    "Id": "Sensors",
    "Name": "SensorCollection",
    "Members": [
        { "@odata.id": "/redfish/v1/Chassis/1/Sensors/Fan.Embedded.1" },
        { "@odata.id": "/redfish/v1/Chassis/1/Sensors/CPU1Temp" },
        { "@odata.id": "/redfish/v1/Chassis/1/Sensors/PS1Voltage" }
    ]
}
JSON;

    private const string FAN_SENSOR_OUTPUT = <<<'JSON'
{
    "@odata.context": "/redfish/v1/$metadata#Sensor.Sensor",
    "@odata.id": "/redfish/v1/Chassis/1/Sensors/Fan.Embedded.1",
    "Id": "Fan.Embedded.1",
    "Name": "System Board Fan 1",
    "ReadingType": "Rotational",
    "ReadingUnits": "RPM",
    "Reading": 6720,
    "Thresholds": {
        "UpperCritical": { "Reading": null },
        "UpperCaution": { "Reading": null },
        "LowerCaution": { "Reading": 960 },
        "LowerCritical": { "Reading": 600 }
    }
}
JSON;

    private const string TEMP_SENSOR_OUTPUT = <<<'JSON'
{
    "@odata.context": "/redfish/v1/$metadata#Sensor.Sensor",
    "@odata.id": "/redfish/v1/Chassis/1/Sensors/CPU1Temp",
    "Id": "CPU1Temp",
    "Name": "CPU1 Temp",
    "ReadingType": "Temperature",
    "Reading": 45.5,
    "Thresholds": {
        "UpperCaution": { "Reading": 80 },
        "UpperCritical": { "Reading": 95 }
    }
}
JSON;

    private const string VOLTAGE_SENSOR_OUTPUT = <<<'JSON'
{
    "@odata.context": "/redfish/v1/$metadata#Sensor.Sensor",
    "@odata.id": "/redfish/v1/Chassis/1/Sensors/PS1Voltage",
    "Id": "PS1Voltage",
    "Name": "PS1 Voltage",
    "ReadingType": "Voltage",
    "Reading": 12.1
}
JSON;

    public function testProbeCrawlsTheSensorsOfAllChassis(): void
    {
        $this->fakeRedfish();
        $readings = $this->probe()->stat('readings', []);

        $this->assertSame(
            ['CPU Temp', 'Inlet Temp', 'Fan 1', 'Power Supply 1'],
            array_column($readings, 'name')
        );
        $this->assertSame(
            ['temperature', 'temperature', 'fanspeed', 'power'],
            array_column($readings, 'class')
        );
        $this->assertEquals(
            [47.0, 25.5, 3600.0, 120.5],
            array_column($readings, 'value')
        );
        $this->assertSame(
            [
                '/Chassis/1/Thermal/CPU Temp',
                '/Chassis/1/Thermal/Inlet Temp',
                '/Chassis/1/Thermal/Fan 1',
                '/Chassis/1/Power/Power Supply 1',
            ],
            array_column($readings, 'source')
        );
        // readings without a value and without thresholds are not crawled
    }

    public function testProbeSkipsMissingResources(): void
    {
        // the chassis has no power resource
        Http::fake(fn (Request $request) => match ($request->url()) {
            'https://bmc.example.com/redfish/v1/Chassis' => Http::response(self::CHASSIS_OUTPUT),
            'https://bmc.example.com/redfish/v1/Chassis/1/Thermal' => Http::response(self::THERMAL_OUTPUT),
            default => Http::response([], 404),
        });
        $readings = $this->probe()->stat('readings', []);

        $this->assertSame(['CPU Temp', 'Inlet Temp', 'Fan 1'], array_column($readings, 'name'));
    }

    public function testProbeUsesTheSensorsCollectionWhenThermalAndPowerAreMissing(): void
    {
        // the chassis has no thermal or power resources, only per-sensor resources (e.g. Dell iDRAC)
        Http::fake(fn (Request $request) => match ($request->url()) {
            'https://bmc.example.com/redfish/v1/Chassis' => Http::response(self::CHASSIS_OUTPUT),
            'https://bmc.example.com/redfish/v1/Chassis/1/Sensors' => Http::response(self::SENSORS_COLLECTION_OUTPUT),
            'https://bmc.example.com/redfish/v1/Chassis/1/Sensors/Fan.Embedded.1' => Http::response(self::FAN_SENSOR_OUTPUT),
            'https://bmc.example.com/redfish/v1/Chassis/1/Sensors/CPU1Temp' => Http::response(self::TEMP_SENSOR_OUTPUT),
            'https://bmc.example.com/redfish/v1/Chassis/1/Sensors/PS1Voltage' => Http::response(self::VOLTAGE_SENSOR_OUTPUT),
            default => Http::response([], 404),
        });
        $readings = $this->probe()->stat('readings', []);

        // voltage readings have no sensor class, they are not crawled
        $this->assertSame(['System Board Fan 1', 'CPU1 Temp'], array_column($readings, 'name'));
        $this->assertSame(['fanspeed', 'temperature'], array_column($readings, 'class'));
        $fan = $readings[0];
        $this->assertEquals(6720.0, $fan['value']);
        $this->assertEquals(960.0, $fan['warn_low']);
        $this->assertEquals(600.0, $fan['crit_low']);
        $this->assertNull($fan['warn_high']);
        $this->assertNull($fan['crit_high']);
        $temperature = $readings[1];
        $this->assertEquals(45.5, $temperature['value']);
        $this->assertEquals(80.0, $temperature['warn_high']);
        $this->assertEquals(95.0, $temperature['crit_high']);
    }

    public function testProbeEnrichesThermalAndPowerWithTheSensorsCollection(): void
    {
        // some BMCs (e.g. Dell) report the temperatures in the thermal resource, but the values
        // and thresholds of the fans only in the per-sensor resources, the power resource
        // only has status entries
        $thermal = <<<'JSON'
{
    "Id": "Thermal",
    "Temperatures": [
        { "Name": "CPU1 Temp", "ReadingCelsius": 72 },
        { "Name": "Inlet Temp", "ReadingCelsius": 19 }
    ],
    "Fans": [
        { "Name": "System Board Fan1", "ReadingRPM": null },
        { "Name": "System Board Fan2", "ReadingRPM": 3600 }
    ]
}
JSON;
        $power = <<<'JSON'
{
    "Id": "Power",
    "PowerSupplies": [
        { "Name": "PS1 Status" },
        { "Name": "PS2 Status" }
    ]
}
JSON;
        $collection = <<<'JSON'
{
    "Id": "Sensors",
    "Members": [
        { "@odata.id": "/redfish/v1/Chassis/1/Sensors/Fan.Embedded.1" },
        { "@odata.id": "/redfish/v1/Chassis/1/Sensors/CPU1Temp" }
    ]
}
JSON;
        $fan = <<<'JSON'
{
    "Id": "Fan.Embedded.1",
    "Name": "System Board Fan1",
    "ReadingType": "Rotational",
    "Reading": 6720,
    "Thresholds": {
        "LowerCaution": { "Reading": 960 },
        "LowerCritical": { "Reading": 600 }
    }
}
JSON;
        $temperature = <<<'JSON'
{
    "Id": "CPU1Temp",
    "Name": "CPU1 Temp",
    "ReadingType": "Temperature",
    "Reading": 45.5,
    "Thresholds": {
        "UpperCaution": { "Reading": 80 },
        "UpperCritical": { "Reading": 95 }
    }
}
JSON;
        Http::fake(fn (Request $request) => match ($request->url()) {
            'https://bmc.example.com/redfish/v1/Chassis' => Http::response(self::CHASSIS_OUTPUT),
            'https://bmc.example.com/redfish/v1/Chassis/1/Thermal' => Http::response($thermal),
            'https://bmc.example.com/redfish/v1/Chassis/1/Power' => Http::response($power),
            'https://bmc.example.com/redfish/v1/Chassis/1/Sensors' => Http::response($collection),
            'https://bmc.example.com/redfish/v1/Chassis/1/Sensors/Fan.Embedded.1' => Http::response($fan),
            'https://bmc.example.com/redfish/v1/Chassis/1/Sensors/CPU1Temp' => Http::response($temperature),
            default => Http::response([], 404),
        });
        $readings = $this->probe()->stat('readings', []);

        $this->assertSame(['CPU1 Temp', 'Inlet Temp', 'System Board Fan2', 'System Board Fan1'], array_column($readings, 'name'));
        // the per-sensor readings take precedence, they carry the current values and the thresholds
        $this->assertEquals(45.5, $readings[0]['value']);
        $this->assertEquals(95.0, $readings[0]['crit_high']);
        $this->assertEquals(80.0, $readings[0]['warn_high']);
        $this->assertEquals(19.0, $readings[1]['value']);
        $this->assertEquals(3600.0, $readings[2]['value']);
        $this->assertEquals(6720.0, $readings[3]['value']);
        $this->assertEquals(960.0, $readings[3]['warn_low']);
        $this->assertEquals(600.0, $readings[3]['crit_low']);
        // the status entries of the power resource are not crawled
    }

    public function testProbeTreatsServerErrorsAsMissingResources(): void
    {
        // some BMCs (e.g. Dell) answer 501 for resources they do not implement
        Http::fake(fn (Request $request) => match ($request->url()) {
            'https://bmc.example.com/redfish/v1/Chassis' => Http::response(self::CHASSIS_OUTPUT),
            'https://bmc.example.com/redfish/v1/Chassis/1/Thermal',
            'https://bmc.example.com/redfish/v1/Chassis/1/Power' => Http::response([], 501),
            'https://bmc.example.com/redfish/v1/Chassis/1/Sensors' => Http::response(self::SENSORS_COLLECTION_OUTPUT),
            'https://bmc.example.com/redfish/v1/Chassis/1/Sensors/Fan.Embedded.1' => Http::response(self::FAN_SENSOR_OUTPUT),
            'https://bmc.example.com/redfish/v1/Chassis/1/Sensors/CPU1Temp' => Http::response(self::TEMP_SENSOR_OUTPUT),
            'https://bmc.example.com/redfish/v1/Chassis/1/Sensors/PS1Voltage' => Http::response(self::VOLTAGE_SENSOR_OUTPUT),
            default => Http::response([], 404),
        });
        $readings = $this->probe()->stat('readings', []);

        $this->assertSame(['System Board Fan 1', 'CPU1 Temp'], array_column($readings, 'name'));
    }

    public function testProbeFollowsTheChassisLinks(): void
    {
        // the chassis resource references its sensor resources, the crawl follows the references
        $chassis = <<<'JSON'
{
    "Id": "System.Embedded.1",
    "Name": "System Chassis",
    "Thermal": "https://bmc.example.com/redfish/v1/Chassis/System.Embedded.1/Thermal",
    "Power": "https://bmc.example.com/redfish/v1/Chassis/System.Embedded.1/Power",
    "Links": {
        "Sensors": "https://bmc.example.com/redfish/v1/Chassis/System.Embedded.1/SensorList"
    }
}
JSON;
        Http::fake(fn (Request $request) => match ($request->url()) {
            'https://bmc.example.com/redfish/v1/Chassis' => Http::response(self::CHASSIS_OUTPUT),
            'https://bmc.example.com/redfish/v1/Chassis/1' => Http::response($chassis),
            'https://bmc.example.com/redfish/v1/Chassis/System.Embedded.1/Thermal',
            'https://bmc.example.com/redfish/v1/Chassis/System.Embedded.1/Power' => Http::response([], 404),
            'https://bmc.example.com/redfish/v1/Chassis/System.Embedded.1/SensorList' => Http::response(self::SENSORS_COLLECTION_OUTPUT),
            'https://bmc.example.com/redfish/v1/Chassis/1/Sensors/Fan.Embedded.1' => Http::response(self::FAN_SENSOR_OUTPUT),
            'https://bmc.example.com/redfish/v1/Chassis/1/Sensors/CPU1Temp' => Http::response(self::TEMP_SENSOR_OUTPUT),
            'https://bmc.example.com/redfish/v1/Chassis/1/Sensors/PS1Voltage' => Http::response(self::VOLTAGE_SENSOR_OUTPUT),
            default => Http::response([], 404),
        });

        $this->probe();

        // the sensors collection is read from the path the chassis resource links to
        Http::assertSent(fn (Request $request) => $request->url() === 'https://bmc.example.com/redfish/v1/Chassis/System.Embedded.1/SensorList');
    }

    public function testProbePrefixesDuplicateNamesAcrossChassis(): void
    {
        Http::fake(fn (Request $request) => match ($request->url()) {
            'https://bmc.example.com/redfish/v1/Chassis' => Http::response(self::CHASSIS_TWO_OUTPUT),
            'https://bmc.example.com/redfish/v1/Chassis/1/Thermal',
            'https://bmc.example.com/redfish/v1/Chassis/2/Thermal' => Http::response(self::THERMAL_OUTPUT),
            default => Http::response([], 404),
        });
        $readings = $this->probe()->stat('readings', []);

        $this->assertSame(
            [
                'Chassis 1 CPU Temp', 'Chassis 1 Inlet Temp', 'Chassis 1 Fan 1',
                'Chassis 2 CPU Temp', 'Chassis 2 Inlet Temp', 'Chassis 2 Fan 1',
            ],
            array_column($readings, 'name')
        );
    }

    public function testProbeUsesChassisIdsWithSpecialCharacters(): void
    {
        Http::fake(fn (Request $request) => match ($request->url()) {
            'https://bmc.example.com/redfish/v1/Chassis' => Http::response(self::CHASSIS_MULTI_OUTPUT),
            'https://bmc.example.com/redfish/v1/Chassis/System.Embedded.1/Thermal' => Http::response(self::THERMAL_OUTPUT),
            default => Http::response([], 404),
        });
        $readings = $this->probe()->stat('readings', []);

        $this->assertSame(['CPU Temp', 'Inlet Temp', 'Fan 1'], array_column($readings, 'name'));
        // the chassis id with a colon is used as-is in the request url
        Http::assertSent(fn (Request $request) => $request->url() === 'https://bmc.example.com/redfish/v1/Chassis/Enclosure.Internal.0-0:RAID.Integrated.1-1/Thermal');
    }

    public function testProbeUsesConfiguredUrlAndAuthentication(): void
    {
        $this->fakeRedfish();

        $this->probe(scheme: 'http', port: 8000, username: 'admin', password: 'secret');

        Http::assertSent(fn (Request $request): bool => $request->url() === 'http://bmc.example.com:8000/redfish/v1/Chassis'
            && ($request->header('Authorization')[0] ?? null) === 'Basic ' . base64_encode('admin:secret'));
    }

    public function testProbeFailsWhenTheServiceIsUnreachable(): void
    {
        Http::fake(fn () => throw new ConnectionException('Connection refused'));
        $result = $this->probe();

        $this->assertFalse($result->isSuccess());
        $this->assertSame('Failed to connect to Redfish on bmc.example.com: Connection refused', $result->errorMessage());
    }

    public function testProbeFailsWhenAuthenticationFails(): void
    {
        Http::fake(fn () => Http::response([], 401));
        $result = $this->probe();

        $this->assertFalse($result->isSuccess());
        $this->assertSame('Redfish authentication failed on bmc.example.com (HTTP 401)', $result->errorMessage());
    }

    public function testProbeFailsWhenTheChassisCollectionIsMissing(): void
    {
        // the service answers, but has no chassis collection (404) or cannot serve it (5xx)
        foreach (['missing collection' => 404, 'server error' => 500] as $status => $httpStatus) {
            Http::fake(fn () => Http::response([], $httpStatus));
            $result = $this->probe();

            $this->assertFalse($result->isSuccess(), $status);
            $this->assertSame('Failed to connect to Redfish on bmc.example.com', $result->errorMessage(), $status);
        }
    }

    private function probe(string $scheme = 'https', ?int $port = null, string $username = '', string $password = ''): ProbeResult
    {
        $method = new RedfishPollingMethod;
        $config = new RedfishConfig(
            hostname: 'bmc.example.com',
            username: $username,
            password: $password,
            scheme: $scheme,
            port: $port,
            insecure: false,
            timeout: 10,
        );

        return $method->probe(new Device(['hostname' => 'bmc.example.com']), $config);
    }

    private function fakeRedfish(): void
    {
        Http::fake(fn (Request $request) => match ($request->url()) {
            'https://bmc.example.com/redfish/v1/Chassis' => Http::response(self::CHASSIS_OUTPUT),
            'https://bmc.example.com/redfish/v1/Chassis/1/Thermal' => Http::response(self::THERMAL_OUTPUT),
            'https://bmc.example.com/redfish/v1/Chassis/1/Power' => Http::response(self::POWER_OUTPUT),
            default => Http::response([], 404),
        });
    }
}
