<?php

/**
 * NodeManagerTest.php
 *
 * Tests for the NodeManager class
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
 * @copyright  2021 Trym Lund Flogard
 * @author     Trym Lund Flogard <trym@flogard.no>
 */

namespace LibreNMS\Tests\Unit;

use Exception;
use LibreNMS\Config;
use LibreNMS\IPMI\IPMIClient;
use LibreNMS\IPMI\NodeManager;
use LibreNMS\Tests\TestCase;

class NodeManagerTest extends TestCase
{
    private const DATA_DIR = 'tests/data/IPMI/NodeManager/';
    private const DATA = [
        '-1' => ['version_unsupported.sdr.bin', 'version_unsupported.json'],
        'corrupt' => ['empty.sdr.bin', 'version_unsupported.json'],
        '1.5' => ['version_1_5.sdr.bin', 'version_1_5.json'],
        // '2.0' => MISSING DATA. Please add test data if you access to Intel Node Manager 2.0+ equipment.
        // '2.5' => MISSING DATA. Please add test data if you access to Intel Node Manager 2.0+ equipment.
        // '3.0' => MISSING DATA. Please add test data if you access to Intel Node Manager 2.0+ equipment.
    ];

    /**
     * @
     */
    private ?string $sdr;
    private ?array $schema;

    public function testIsPlatformSupported_SDRNoIntelRecord_IsFalse(): void
    {
        $expected = false;
        $client = $this->getMock('-1');

        $sut = new NodeManager($client);
        $actual = $sut->isPlatformSupported();

        $this->assertEquals($expected, $actual, 'Expected the platform to be unsupported, but returned true.');
    }

    public function testIsPlatformSupported_SDRMissing_IsFalse(): void
    {
        $expected = false;
        $client = $this->getMock('corrupt');
        $this->sdr = null;

        $sut = new NodeManager($client);
        $actual = $sut->isPlatformSupported();

        $this->assertEquals($expected, $actual, 'Expected the platform to be unsupported, but returned true.');
    }

    public function testIsPlatformSupported_SDRContainsIntelRecord_IsTrue(): void
    {
        $expected = true;
        $client = $this->getMock('1.5');

        $sut = new NodeManager($client);
        $actual = $sut->isPlatformSupported();

        $this->assertEquals($expected, $actual, 'Expected the platform to be supported, but returned false.');
    }

    public function testPollSeonsors_PlatformNotSupported_EmptyArray(): void
    {
        $expected = [];
        $client = $this->getMock('-1');

        $sut = new NodeManager($client);
        $actual = $sut->pollSeonsors();

        $this->assertEquals(0, sizeof($actual), 'Expected power readings to be an empty array, but was not empty.');
        $this->assertEquals($expected, $actual);
    }

    public function testPollSeonsors_Version15_PlatformReadingOnly(): void
    {
        $expectedKey = ['Intel ME Platform'];
        $client = $this->getMock('1.5');

        $sut = new NodeManager($client);
        $response = $sut->pollSeonsors();
        $actualKeys = array_keys($response);

        $this->assertEquals(sizeof($expectedKey), sizeof($actualKeys), 'Expected one sensor to be returned.');
        $this->assertEquals($expectedKey[0], $actualKeys[0], 'Expected only platform sensor to be returned.');
    }

    public function testPollSeonsors_Version15_PlatformReadingCorrect(): void
    {
        $client = $this->getMock('1.5');
        $expectedValue = $this->schema['platform_global_power']['expected'];

        $sut = new NodeManager($client);
        $response = $sut->pollSeonsors();
        $actualValue = $response['Intel ME Platform'];

        $this->assertEquals($expectedValue, $actualValue, "Expected power reading to be $expectedValue watts.");
    }

    public function testDiscoverSensors_PlatformNotSupported_EmptyArray(): void
    {
        $expected = [];
        $client = $this->getMock('-1');

        $sut = new NodeManager($client);
        $actual = $sut->discoverSensors();

        $this->assertEquals(0, sizeof($actual), 'Expected no available sensors to be returned.');
        $this->assertEquals($expected, $actual, 'Expected no available sensors to be returned.');
    }

    public function testDiscoverSensors_Version15_PlatformSensorOnly(): void
    {
        $expectedOid = 'platform';
        $client = $this->getMock('1.5');

        $sut = new NodeManager($client);
        $response = $sut->discoverSensors();

        $this->assertEquals(1, sizeof($response), 'Expected one sensor to be returned.');
        $this->assertEquals($expectedOid, $response[0][0], 'Expected platform sensor to be the only available sensor.');
    }

    protected function tearDown(): void
    {
        $this->sdr = null;
        $this->schema = null;
    }

    private static function loadData(string $name): string
    {
        $path = Config::get('install_dir') . '/' . NodeManagerTest::DATA_DIR . $name;

        return file_get_contents($path);
    }

    private function getMock(string $version): IPMIClient
    {
        switch ($version) {
            case '-1':
            case 'corrupt':
            case '1.5':
                $this->sdr = NodeManagerTest::loadData(NodeManagerTest::DATA[$version][0]);
                $this->schema = json_decode(NodeManagerTest::loadData(NodeManagerTest::DATA[$version][1]), true);

                return $this->createIPMIMock();

                case '2.0':
                case '2.5':
                case '3.0':
                throw new Exception("Test data missing for version $version");
            default:
                throw new Exception('Version is not known');
        }
    }

    private function createIPMIMock(): IPMIClient
    {
        $mock = \Mockery::mock('LibreNMS\IPMI\IPMIClient');
        $mock->shouldReceive('getRawSDR')->andReturn($this->sdr);
        $sendCommand = function ($command, $escalatePrivileges) {
            foreach ($this->schema as $key => $value) {
                if (preg_match($value['requestPattern'], $command)) {
                    NodeManagerTest::validateSlaveAndChannel($value, $command);

                    return $value['response'];
                }
            }

            throw new Exception("IPMI command '$command' not found in schema.");
        };
        $mock->shouldReceive('sendCommand')
            ->andReturnUsing($sendCommand);

        return $mock;
    }

    private static function validateSlaveAndChannel(?array $schema, ?string $command): void
    {
        if (! preg_match('/-t ' . $schema['slave'] . '/', $command)) {
            throw new Exception('IPMI command has an incorrect slave address.');
        }

        if (! preg_match('/-b ' . $schema['channel'] . '/', $command)) {
            throw new Exception('IPMI command has an incorrect channel.');
        }
    }
}
