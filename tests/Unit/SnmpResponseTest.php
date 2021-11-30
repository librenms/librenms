<?php
/**
 * SnmpResponseTest.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit;

use LibreNMS\Config;
use LibreNMS\Data\Source\SnmpResponse;
use LibreNMS\Tests\TestCase;

class SnmpResponseTest extends TestCase
{
    public function testSimple(): void
    {
        $response = new SnmpResponse("IF-MIB::ifDescr[1] = lo\nIF-MIB::ifDescr[2] = enp4s0\n");

        $this->assertTrue($response->isValid());
        $this->assertEquals(['IF-MIB::ifDescr[1]' => 'lo', 'IF-MIB::ifDescr[2]' => 'enp4s0'], $response->values());
        $this->assertEquals('lo', $response->value());
        $this->assertEquals(['IF-MIB::ifDescr' => [1 => 'lo', 2 => 'enp4s0']], $response->table());
        $this->assertEquals([1 => ['IF-MIB::ifDescr' => 'lo'], 2 => ['IF-MIB::ifDescr' => 'enp4s0']], $response->table(1));

        // snmptranslate type response
        $response = new SnmpResponse("IF-MIB::ifDescr\n");

        $this->assertTrue($response->isValid());
        $this->assertEquals(['' => 'IF-MIB::ifDescr'], $response->values());
        $this->assertEquals('IF-MIB::ifDescr', $response->value());
        $this->assertEquals(['IF-MIB::ifDescr'], $response->table());

        // unescaped strings
        $response = new SnmpResponse("Q-BRIDGE-MIB::dot1qVlanStaticName[1] = \"\\default\\\"\nQ-BRIDGE-MIB::dot1qVlanStaticName[6] = \\single\\\nQ-BRIDGE-MIB::dot1qVlanStaticName[9] = \\\\double\\\\\n");
        $this->assertTrue($response->isValid());
        $this->assertEquals('default', $response->value());
        Config::set('snmp.unescape', false);
        $this->assertEquals([
            'Q-BRIDGE-MIB::dot1qVlanStaticName[1]' => 'default',
            'Q-BRIDGE-MIB::dot1qVlanStaticName[6]' => '\\single\\',
            'Q-BRIDGE-MIB::dot1qVlanStaticName[9]' => '\\\\double\\\\',
        ], $response->values());
        $this->assertEquals(['Q-BRIDGE-MIB::dot1qVlanStaticName' => [
            1 => 'default',
            6 => '\\single\\',
            9 => '\\\\double\\\\',
        ]], $response->table());
        Config::set('snmp.unescape', true); // for buggy versions of net-snmp
        $this->assertEquals([
            'Q-BRIDGE-MIB::dot1qVlanStaticName[1]' => 'default',
            'Q-BRIDGE-MIB::dot1qVlanStaticName[6]' => 'single',
            'Q-BRIDGE-MIB::dot1qVlanStaticName[9]' => '\\double\\',
        ], $response->values());
        $this->assertEquals(['Q-BRIDGE-MIB::dot1qVlanStaticName' => [
            1 => 'default',
            6 => 'single',
            9 => '\\double\\',
        ]], $response->table());
    }

    public function testMultiLine(): void
    {
        $response = new SnmpResponse("SNMPv2-MIB::sysDescr.0 = \"something\n on two lines\"\n");

        $this->assertTrue($response->isValid());
        $this->assertEquals("something\n on two lines", $response->value());
        $this->assertEquals(['SNMPv2-MIB::sysDescr.0' => "something\n on two lines"], $response->values());
    }

    public function numericTest(): void
    {
        $response = new SnmpResponse(".1.3.6.1.2.1.2.2.1.10.1 = 495813425\n.1.3.6.1.2.1.2.2.1.10.2 = 3495809228\n");

        $this->assertTrue($response->isValid());
        $this->assertEquals('496255256', $response->value());
        $this->assertEquals(['.1.3.6.1.2.1.2.2.1.10.1' => '496255256', '.1.3.6.1.2.1.2.2.1.10.2' => '3495809228'], $response->values());
        $this->assertEquals(['.1.3.6.1.2.1.2.2.1.10.1' => '496255256', '.1.3.6.1.2.1.2.2.1.10.2' => '3495809228'], $response->table());
        $this->assertEquals(['.1.3.6.1.2.1.2.2.1.10.1' => '496255256', '.1.3.6.1.2.1.2.2.1.10.2' => '3495809228'], $response->table(3));
    }

    public function tableTest(): void
    {
        $response = new SnmpResponse('HOST-RESOURCES-MIB::hrStorageIndex.1 = 1
HOST-RESOURCES-MIB::hrStorageIndex.34 = 34
HOST-RESOURCES-MIB::hrStorageIndex.36 = 36
HOST-RESOURCES-MIB::hrStorageType.1 = HOST-RESOURCES-TYPES::hrStorageRam
HOST-RESOURCES-MIB::hrStorageType.34 = HOST-RESOURCES-TYPES::hrStorageFixedDisk
HOST-RESOURCES-MIB::hrStorageType.36 = HOST-RESOURCES-TYPES::hrStorageFixedDisk
HOST-RESOURCES-MIB::hrStorageDescr.1 = Physical memory
HOST-RESOURCES-MIB::hrStorageDescr.34 = /run
HOST-RESOURCES-MIB::hrStorageDescr.36 = /
HOST-RESOURCES-MIB::hrStorageAllocationUnits.1 = 1024 Bytes
HOST-RESOURCES-MIB::hrStorageAllocationUnits.34 = 4096 Bytes
HOST-RESOURCES-MIB::hrStorageAllocationUnits.36 = 4096 Bytes
HOST-RESOURCES-MIB::hrStorageSize.1 = 12136128
HOST-RESOURCES-MIB::hrStorageSize.34 = 1517016
HOST-RESOURCES-MIB::hrStorageSize.36 = 193772448
HOST-RESOURCES-MIB::hrStorageUsed.1 = 11577192
HOST-RESOURCES-MIB::hrStorageUsed.34 = 429
HOST-RESOURCES-MIB::hrStorageUsed.36 = 127044934
');

        $this->assertTrue($response->isValid());
        $this->assertEquals('34', $response->value());
        $this->assertEquals([
            'HOST-RESOURCES-MIB::hrStorageIndex.1' => '1',
            'HOST-RESOURCES-MIB::hrStorageIndex.34' => '34',
            'HOST-RESOURCES-MIB::hrStorageIndex.36' => '36',
            'HOST-RESOURCES-MIB::hrStorageType.1' => 'HOST-RESOURCES-TYPES::hrStorageRam',
            'HOST-RESOURCES-MIB::hrStorageType.34' => 'HOST-RESOURCES-TYPES::hrStorageFixedDisk',
            'HOST-RESOURCES-MIB::hrStorageType.36' => 'HOST-RESOURCES-TYPES::hrStorageFixedDisk',
            'HOST-RESOURCES-MIB::hrStorageDescr.1' => 'Physical memory',
            'HOST-RESOURCES-MIB::hrStorageDescr.34' => '/run',
            'HOST-RESOURCES-MIB::hrStorageDescr.36' => '/',
            'HOST-RESOURCES-MIB::hrStorageAllocationUnits.1' => '1024',
            'HOST-RESOURCES-MIB::hrStorageAllocationUnits.34' => '4096',
            'HOST-RESOURCES-MIB::hrStorageAllocationUnits.36' => '4096',
            'HOST-RESOURCES-MIB::hrStorageSize.1' => '12136128',
            'HOST-RESOURCES-MIB::hrStorageSize.34 ' => '1517016',
            'HOST-RESOURCES-MIB::hrStorageSize.36 ' => '193772448',
            'HOST-RESOURCES-MIB::hrStorageUsed.1 =' => '11577192',
            'HOST-RESOURCES-MIB::hrStorageUsed.34 ' => '429',
            'HOST-RESOURCES-MIB::hrStorageUsed.36' => '127044934',
        ], $response->values());
        $this->assertEquals([
            '1' => [
                'HOST-RESOURCES-MIB::hrStorageIndex' => '1',
                'HOST-RESOURCES-MIB::hrStorageType' => 'HOST-RESOURCES-TYPES::hrStorageRam',
                'HOST-RESOURCES-MIB::hrStorageDescr' => 'Physical memory',
                'HOST-RESOURCES-MIB::hrStorageAllocationUnits' => '1024',
                'HOST-RESOURCES-MIB::hrStorageSize' => '12136128',
                'HOST-RESOURCES-MIB::hrStorageUsed' => '11577192',
            ],
            '34' => [
                'HOST-RESOURCES-MIB::hrStorageIndex' => '34',
                'HOST-RESOURCES-MIB::hrStorageType' => 'HOST-RESOURCES-TYPES::hrStorageFixedDisk',
                'HOST-RESOURCES-MIB::hrStorageDescr' => '/run',
                'HOST-RESOURCES-MIB::hrStorageAllocationUnits' => '4096',
                'HOST-RESOURCES-MIB::hrStorageSize' => '1517016',
                'HOST-RESOURCES-MIB::hrStorageUsed' => '429',
            ],
            '36' => [
                'HOST-RESOURCES-MIB::hrStorageIndex' => '36',
                'HOST-RESOURCES-MIB::hrStorageType' => 'HOST-RESOURCES-TYPES::hrStorageFixedDisk',
                'HOST-RESOURCES-MIB::hrStorageDescr' => '/',
                'HOST-RESOURCES-MIB::hrStorageAllocationUnits' => '4096',
                'HOST-RESOURCES-MIB::hrStorageSize' => '193772448',
                'HOST-RESOURCES-MIB::hrStorageUsed' => '127044934',
            ],
        ], $response->table());
        $this->assertEquals([
            'HOST-RESOURCES-MIB::hrStorageIndex' => [
                '1' => '1',
                '34' => '34',
                '36' => '36',
            ],
            'HOST-RESOURCES-MIB::hrStorageType' => [
                '1' => 'HOST-RESOURCES-TYPES::hrStorageRam',
                '34' => 'HOST-RESOURCES-TYPES::hrStorageFixedDisk',
                '36' => 'HOST-RESOURCES-TYPES::hrStorageFixedDisk',
            ],
            'HOST-RESOURCES-MIB::hrStorageDescr' => [
                '1' => 'Physical memory',
                '34' => '/run',
                '36' => '/',
            ],
            'HOST-RESOURCES-MIB::hrStorageAllocationUnits' => [
                '1' => '1024',
                '34' => '4096',
                '36' => '4096',
            ],
            'HOST-RESOURCES-MIB::hrStorageSize' => [
                '1' => '12136128',
                '34' => '1517016',
                '36' => '193772448',
            ],
            'HOST-RESOURCES-MIB::hrStorageUsed' => [
                '1' => '11577192',
                '34' => '429',
                '36' => '127044934',
            ],
        ], $response->table(1));
    }

    public function trimTest(): void
    {
        $response = new SnmpResponse(".1.3.6.1.2.1.2.2.1.10.1 = \\\"4958\\\"\n.1.3.6.1.2.1.2.2.1.10.2 = \"\" 349\r\n\n");
        $this->assertTrue($response->isValid());
        $this->assertEquals('4958', $response->value());
        $this->assertEquals(['.1.3.6.1.2.1.2.2.1.10.1' => '4958', '.1.3.6.1.2.1.2.2.1.10.2' => '349'], $response->values());

        $response = new SnmpResponse(".1.3.6.1.2.1.31.1.1.1.18.1 = \"internal\\\\backslash\"\n");
        $this->assertTrue($response->isValid());
        $this->assertEquals('internal\\backslash', $response->value());
    }

    public function testErrorHandling(): void
    {
        // no response
        $response = new SnmpResponse('', "Timeout: No Response from udp:127.1.6.1:1161.\n", 1);
        $this->assertFalse($response->isValid());
        $this->assertEquals('Timeout: No Response from udp:127.1.6.1:1161.', $response->getErrorMessage());

        // correct handling of empty output
        $this->assertEmpty($response->value());
        $this->assertEmpty($response->values());
        $this->assertEmpty($response->table());

        // invalid type (should ignore)
        $response = new SnmpResponse("SNMPv2-MIB::sysObjectID.0 = Wrong Type (should be OBJECT IDENTIFIER): wrong thing\n");
        $this->assertTrue($response->isValid());
        $this->assertEquals('', $response->getErrorMessage());
        $this->assertEquals(['SNMPv2-MIB::sysObjectID.0' => 'wrong thing'], $response->values());

        // No more variables left in this MIB View
        $response = new SnmpResponse("iso.9 = No more variables left in this MIB View (It is past the end of the MIB tree)\n");
        $this->assertFalse($response->isValid());
        $this->assertEquals('No more variables left in this MIB View (It is past the end of the MIB tree)', $response->getErrorMessage());

        // No Such Instance currently exists at this OID.
        $response = new SnmpResponse("SNMPv2-SMI::enterprises.9.9.661.1.3.2.1.1 = No Such Instance currently exists at this OID.\n");
        $this->assertFalse($response->isValid());
        $this->assertEquals('No Such Instance currently exists at this OID.', $response->getErrorMessage());

        // Unknown user name
        $response = new SnmpResponse('', "snmpget: Unknown user name (Sub-id not found: (top) -> sysDescr)\n", 1);
        $this->assertFalse($response->isValid());
        $this->assertEquals('Unknown user name', $response->getErrorMessage());

        // Authentication failure
        $response = new SnmpResponse('', "snmpget: Authentication failure (incorrect password, community or key) (Sub-id not found: (top) -> sysDescr)\n", 1);
        $this->assertFalse($response->isValid());
        $this->assertEquals('Authentication failure', $response->getErrorMessage());

        // OID not increasing
        $response = new SnmpResponse(".1.3.6.1.2.1.2.2.1.1.1 = INTEGER: 1\n", "Error: OID not increasing: .1.3.6.1.2.100.2.2.1.1\n >= .1.3.6.1.2.1.2.2.1.1.1\n", 1);
        $this->assertFalse($response->isValid());
        $this->assertEquals('Error: OID not increasing: .1.3.6.1.2.100.2.2.1.1', $response->getErrorMessage());
    }
}
