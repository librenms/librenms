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

use App\Facades\LibrenmsConfig;
use LibreNMS\Data\Source\Snmp\RawSnmpResponse;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Tests\TestCase;

final class SnmpResponseTest extends TestCase
{
    public function testInfersOutputEncoding(): void
    {
        $response = new RawSnmpResponse("IF-MIB::ifDescr[1] = \xD8verbyvegen\n");

        $this->assertSame('Øverbyvegen', $response->value());
        $this->assertSame("IF-MIB::ifDescr[1] = \xD8verbyvegen\n", $response->raw());
        $this->assertSame(['IF-MIB::ifDescr[1]' => 'Øverbyvegen'], $response->values());
    }

    public function testSimple(): void
    {
        $response = new RawSnmpResponse("IF-MIB::ifDescr[1] = lo\nIF-MIB::ifDescr[2] = enp4s0\n");

        $this->assertTrue($response->isValid());
        $this->assertEquals(['IF-MIB::ifDescr[1]' => 'lo', 'IF-MIB::ifDescr[2]' => 'enp4s0'], $response->values());
        $this->assertEquals('lo', $response->value());
        $this->assertEquals(['IF-MIB::ifDescr' => [1 => 'lo', 2 => 'enp4s0']], $response->table());
        $this->assertEquals([1 => ['IF-MIB::ifDescr' => 'lo'], 2 => ['IF-MIB::ifDescr' => 'enp4s0']], $response->table(1));

        // snmptranslate type response
        $response = new RawSnmpResponse("IF-MIB::ifDescr\n");

        $this->assertTrue($response->isValid());
        $this->assertEquals(['' => 'IF-MIB::ifDescr'], $response->values());
        $this->assertEquals('IF-MIB::ifDescr', $response->value());
        $this->assertEquals(['' => 'IF-MIB::ifDescr'], $response->table());

        // unescaped strings
        $response = new RawSnmpResponse("Q-BRIDGE-MIB::dot1qVlanStaticName[1] = \"\\default\\\"\nQ-BRIDGE-MIB::dot1qVlanStaticName[6] = \\single\\\nQ-BRIDGE-MIB::dot1qVlanStaticName[9] = \\\\double\\\\\n");
        $this->assertTrue($response->isValid());
        $this->assertEquals('default', $response->value());
        LibrenmsConfig::set('snmp.unescape', false);
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

        LibrenmsConfig::set('snmp.unescape', true); // for buggy versions of net-snmp
        $response = new RawSnmpResponse("Q-BRIDGE-MIB::dot1qVlanStaticName[1] = \"\\default\\\"\nQ-BRIDGE-MIB::dot1qVlanStaticName[6] = \\single\\\nQ-BRIDGE-MIB::dot1qVlanStaticName[9] = \\\\double\\\\\n");
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

    public function testValueFetching(): void
    {
        $response = new RawSnmpResponse("IF-MIB::ifDescr[1] = lo\nIF-MIB::ifDescr[2] = enp4s0\nIF-MIB::ifAlias[1] = alias one\nIF-MIB::ifAlias[2] = alias two\n\n");

        $this->assertEquals('lo', $response->value());
        $this->assertEquals('lo', $response->value('IF-MIB::ifDescr[1]'));
        $this->assertEquals('enp4s0', $response->value('IF-MIB::ifDescr[2]'));
        $this->assertEquals('enp4s0', $response->value('IF-MIB::ifDescr.2'));
        $this->assertEquals('lo', $response->value(['IF-MIB::ifDescr[1]', 'IF-MIB::ifDescr[2]']));
        $this->assertEquals('enp4s0', $response->value(['IF-MIB::ifName[2]', 'IF-MIB::ifDescr[2]', 'IF-MIB::ifDescr[1]']));
        $this->assertEquals('lo', $response->value(['IF-MIB::ifDescr.1', 'IF-MIB::ifDescr.2']));

        $this->assertEquals('lo', $response->value('IF-MIB::ifDescr'));
        $this->assertEquals('alias one', $response->value('IF-MIB::ifAlias'));
        $this->assertEquals('', $response->value('ifAlias'));
        $this->assertEquals('', $response->value('IF-MIB:'));
        $this->assertEquals('', $response->value('IF-MIB::ifA'));

        $response = new RawSnmpResponse("ifName.1 = lo\nifAlias.3 = cust42\nifAlias.4 = cust51\n\n");
        $this->assertEquals('lo', $response->value('ifName'));
        $this->assertEquals('lo', $response->value('ifName'));
        $this->assertEquals('cust42', $response->value('ifAlias'));
        $this->assertEquals('cust51', $response->value('ifAlias.4'));
        $this->assertEquals(null, $response->value('ifAlias[4]'));
    }

    public function testEmptyValues(): void
    {
        // empty values
        $response = new RawSnmpResponse("IF-MIB::ifAlias[1] = \nIF-MIB::ifAlias[2] = 0\nIF-MIB::ifAlias[3] = \"\"\n\n");
        $this->assertTrue($response->isValid());
        $this->assertEquals('', $response->value());
        $this->assertEquals('', $response->value('IF-MIB::ifAlias[1]'));
        $this->assertEquals('0', $response->value('IF-MIB::ifAlias[2]'));
        $this->assertEquals('', $response->value('IF-MIB::ifAlias[3]'));
        $this->assertEquals('0', $response->value(['IF-MIB::ifAlias[1]', 'IF-MIB::ifAlias[2]', 'IF-MIB::ifAlias[3]']));
    }

    public function testValuesByIndex(): void
    {
        $response = new RawSnmpResponse("IF-MIB::ifIndex[1] = 1\nIF-MIB::ifIndex[2] = 2\nIF-MIB::ifDescr[1] = lo\nIF-MIB::ifDescr[2] = enp4s0\n\n");

        $this->assertTrue($response->isValid());
        $this->assertEquals([
            1 => [
                'IF-MIB::ifIndex' => '1',
                'IF-MIB::ifDescr' => 'lo',
            ],
            2 => [
                'IF-MIB::ifIndex' => '2',
                'IF-MIB::ifDescr' => 'enp4s0',
            ],
        ], $response->valuesByIndex());

        $existing = [0 => 'first', 1 => ['IF-MIB::ifDescr' => 'previous', 'IF-MIB::ifName' => 'lo']];
        $this->assertEquals([
            0 => 'first',
            1 => [
                'IF-MIB::ifIndex' => '1',
                'IF-MIB::ifDescr' => 'lo',
                'IF-MIB::ifName' => 'lo',
            ],
            2 => [
                'IF-MIB::ifIndex' => '2',
                'IF-MIB::ifDescr' => 'enp4s0',
            ],
        ], $response->valuesByIndex($existing));
    }

    public function testGroupByIndex(): void
    {
        $response = new RawSnmpResponse(".1.3.6.1.2.1.2.2.1.10.1 = 495813425\n.1.3.6.1.2.1.2.2.1.10.2 = 3495809228\n");
        $this->assertTrue($response->isValid());
        $this->assertEquals([1 => ['.1.3.6.1.2.1.2.2.1.10.1' => 495813425], 2 => ['.1.3.6.1.2.1.2.2.1.10.2' => 3495809228]], $response->groupByIndex());
        $this->assertEquals(['1.10.1' => ['.1.3.6.1.2.1.2.2.1.10.1' => 495813425], '1.10.2' => ['.1.3.6.1.2.1.2.2.1.10.2' => 3495809228]], $response->groupByIndex(3));
        $this->assertEquals(['6.1.2.1.2.2.1.10.1' => ['.1.3.6.1.2.1.2.2.1.10.1' => 495813425], '6.1.2.1.2.2.1.10.2' => ['.1.3.6.1.2.1.2.2.1.10.2' => 3495809228]], $response->groupByIndex(-2));

        $response = new RawSnmpResponse(".1.3.6.1.2.1.2.2.1.10.1 = 495813425\n.1.3.6.1.2.1.2.2.1.11.1 = 3495809228\n");
        $this->assertEquals([1 => ['.1.3.6.1.2.1.2.2.1.10.1' => 495813425, '.1.3.6.1.2.1.2.2.1.11.1' => 3495809228]], $response->groupByIndex());
        $this->assertEquals(['10.1' => ['.1.3.6.1.2.1.2.2.1.10.1' => 495813425], '11.1' => ['.1.3.6.1.2.1.2.2.1.11.1' => 3495809228]], $response->groupByIndex(2));
        $this->assertEquals(['1.2.2.1.10.1' => ['.1.3.6.1.2.1.2.2.1.10.1' => 495813425], '1.2.2.1.11.1' => ['.1.3.6.1.2.1.2.2.1.11.1' => 3495809228]], $response->groupByIndex(-5));

        $response = new RawSnmpResponse("SOME-MIB::oid.1.1.0 = 14\nSOME-MIB::oid.1.2.0 = 42\n");
        $this->assertTrue($response->isValid());
        $this->assertEquals([0 => ['SOME-MIB::oid.1.1.0' => '14', 'SOME-MIB::oid.1.2.0' => '42']], $response->groupByIndex());
        $this->assertEquals(['1.0' => ['SOME-MIB::oid.1.1.0' => '14'], '2.0' => ['SOME-MIB::oid.1.2.0' => '42']], $response->groupByIndex(2));
        $this->assertEquals(['1.1.0' => ['SOME-MIB::oid.1.1.0' => '14'], '1.2.0' => ['SOME-MIB::oid.1.2.0' => '42']], $response->groupByIndex(-1));
    }

    public function testMultiLine(): void
    {
        $response = new RawSnmpResponse("SNMPv2-MIB::sysDescr.1 = \"something\n on two lines\"\n");

        $this->assertTrue($response->isValid());
        $this->assertEquals("something\n on two lines", $response->value());
        $this->assertEquals(['SNMPv2-MIB::sysDescr.1' => "something\n on two lines"], $response->values());
        $this->assertEquals(['SNMPv2-MIB::sysDescr' => [1 => "something\n on two lines"]], $response->table());
    }

    public function numericTest(): void
    {
        $response = new RawSnmpResponse(".1.3.6.1.2.1.2.2.1.10.1 = 495813425\n.1.3.6.1.2.1.2.2.1.10.2 = 3495809228\n");

        $this->assertTrue($response->isValid());
        $this->assertEquals('496255256', $response->value());
        $this->assertEquals(['.1.3.6.1.2.1.2.2.1.10.1' => '496255256', '.1.3.6.1.2.1.2.2.1.10.2' => '3495809228'], $response->values());
        $this->assertEquals(['.1.3.6.1.2.1.2.2.1.10.1' => '496255256', '.1.3.6.1.2.1.2.2.1.10.2' => '3495809228'], $response->table());
        $this->assertEquals(['.1.3.6.1.2.1.2.2.1.10.1' => '496255256', '.1.3.6.1.2.1.2.2.1.10.2' => '3495809228'], $response->table(3));
    }

    public function tableTest(): void
    {
        $response = new RawSnmpResponse('HOST-RESOURCES-MIB::hrStorageIndex.1 = 1
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
        $response = new RawSnmpResponse(".1.3.6.1.2.1.2.2.1.10.1 = \\\"4958\\\"\n.1.3.6.1.2.1.2.2.1.10.2 = \"\" 349\r\n\n");
        $this->assertTrue($response->isValid());
        $this->assertEquals('4958', $response->value());
        $this->assertEquals(['.1.3.6.1.2.1.2.2.1.10.1' => '4958', '.1.3.6.1.2.1.2.2.1.10.2' => '349'], $response->values());

        $response = new RawSnmpResponse(".1.3.6.1.2.1.31.1.1.1.18.1 = \"internal\\\\backslash\"\n");
        $this->assertTrue($response->isValid());
        $this->assertEquals('internal\\backslash', $response->value());
    }

    public function testErrorHandling(): void
    {
        // no response
        $response = new RawSnmpResponse('', "Timeout: No Response from udp:127.1.6.1:1161.\n", 1);
        $this->assertFalse($response->isValid());
        $this->assertEquals('Timeout: No Response from udp:127.1.6.1:1161.', $response->getErrorMessage());

        // correct handling of empty output
        $response = new RawSnmpResponse('');
        $this->assertFalse($response->isValid());
        $this->assertEquals('Empty Output', $response->getErrorMessage());
        $this->assertEmpty($response->value());
        $this->assertEmpty($response->values());
        $this->assertEmpty($response->table());

        // invalid type (should ignore)
        $response = new RawSnmpResponse("SNMPv2-MIB::sysObjectID.0 = Wrong Type (should be OBJECT IDENTIFIER): wrong thing\n");
        $this->assertTrue($response->isValid());
        $this->assertEquals('', $response->getErrorMessage());
        $this->assertEquals(['SNMPv2-MIB::sysObjectID.0' => 'wrong thing'], $response->values());

        // No more variables left in this MIB View
        $response = new RawSnmpResponse("iso.9 = No more variables left in this MIB View (It is past the end of the MIB tree)\n");
        $this->assertFalse($response->isValid());
        $this->assertEquals('No more variables left in this MIB View (It is past the end of the MIB tree)', $response->getErrorMessage());

        // No Such Instance currently exists at this OID.
        $response = new RawSnmpResponse("SNMPv2-SMI::enterprises.9.9.661.1.3.2.1.1 = No Such Instance currently exists at this OID.\n");
        $this->assertFalse($response->isValid());
        $this->assertEquals('No Such Instance currently exists at this OID.', $response->getErrorMessage());

        // No Such Object available on this agent at this OID
        $response = new RawSnmpResponse("SNMPv2-MIB::sysDescr.0 = No Such Object available on this agent at this OID\n");
        $this->assertFalse($response->isValid());
        $this->assertEquals('No Such Object available on this agent at this OID', $response->getErrorMessage());

        // Unknown user name
        $response = new RawSnmpResponse('', "snmpget: Unknown user name (Sub-id not found: (top) -> sysDescr)\n", 1);
        $this->assertFalse($response->isValid());
        $this->assertEquals('Unknown user name', $response->getErrorMessage());

        // Authentication failure
        $response = new RawSnmpResponse('', "snmpget: Authentication failure (incorrect password, community or key) (Sub-id not found: (top) -> sysDescr)\n", 1);
        $this->assertFalse($response->isValid());
        $this->assertEquals('Authentication failure', $response->getErrorMessage());

        // OID not increasing
        $response = new RawSnmpResponse(".1.3.6.1.2.1.2.2.1.1.1 = INTEGER: 1\n", "Error: OID not increasing: .1.3.6.1.2.100.2.2.1.1\n >= .1.3.6.1.2.1.2.2.1.1.1\n", 1);
        $this->assertFalse($response->isValid());
        $this->assertEquals('Error: OID not increasing: .1.3.6.1.2.100.2.2.1.1', $response->getErrorMessage());

        // NULL return
        $response = new RawSnmpResponse("hrDeviceTable = NULL\n", '', 0);
        $this->assertTrue($response->isValid());
        $response->mapTable(function (): void {
            $this->fail('There should be no data in the array.');
        });

        // Partial response with valid and invalid values
        $response = new RawSnmpResponse("IF-MIB::ifDescr[1] = lo\nIF-MIB::ifDescr[2] = No Such Instance currently exists at this OID.\n");
        $this->assertFalse($response->isValid());
        $this->assertTrue($response->isValid(ignore_partial: true));
        $this->assertSame(['IF-MIB::ifDescr[1]' => 'lo'], $response->values());

        // Multiple errors in raw output (first error captured)
        $response = new RawSnmpResponse("sysDescr.0 = No Such Instance currently exists at this OID.\nsysName.0 = No Such Object available on this agent at this OID\n");
        $this->assertFalse($response->isValid());
        $this->assertEquals('No Such Instance currently exists at this OID.', $response->getErrorMessage());
    }

    public function testFromValues(): void
    {
        $values = [
            'IF-MIB::ifDescr[1]' => 'lo',
            'IF-MIB::ifDescr[2]' => 'enp4s0',
        ];

        $response = new SnmpResponse($values);

        $this->assertTrue($response->isValid());
        $this->assertSame($values, $response->values());
        $this->assertSame('lo', $response->value());
        $this->assertSame('enp4s0', $response->value('IF-MIB::ifDescr[2]'));
    }

    public function testFromValuesWithMissingInstance(): void
    {
        $values = [
            '1.3.6.1.2.1.2.2.1.2.99' => 'No Such Instance currently exists at this OID',
        ];

        $response = new SnmpResponse($values);

        $this->assertFalse($response->isValid());
        $this->assertSame([], $response->values());
        $this->assertSame('No Such Instance currently exists at this OID', $response->getErrorMessage());
    }

    public function testSnmpResponseErrorHandling(): void
    {
        // Empty values
        $response = new SnmpResponse([]);
        $this->assertFalse($response->isValid());
        $this->assertSame('Empty Output', $response->getErrorMessage());
        $this->assertSame([], $response->values());

        // Timeout via stderr
        $response = new SnmpResponse([], "Timeout: No Response from udp:127.1.6.1:1161.\n", 1);
        $this->assertFalse($response->isValid());
        $this->assertSame('Timeout: No Response from udp:127.1.6.1:1161.', $response->getErrorMessage());

        // Authentication failure via stderr
        $response = new SnmpResponse([], "snmpget: Authentication failure (incorrect password)\n", 1);
        $this->assertFalse($response->isValid());
        $this->assertSame('Authentication failure', $response->getErrorMessage());

        // Unknown user name via stderr
        $response = new SnmpResponse([], "snmpget: Unknown user name\n", 1);
        $this->assertFalse($response->isValid());
        $this->assertSame('Unknown user name', $response->getErrorMessage());

        // OID not increasing via stderr
        $response = new SnmpResponse(['1.3.6.1.2.1.1.1.0' => 'Linux'], "Error: OID not increasing: .1.3.6.1.2.100\n", 1);
        $this->assertFalse($response->isValid());
        $this->assertSame('Error: OID not increasing: .1.3.6.1.2.100', $response->getErrorMessage());

        // No Such Object
        $response = new SnmpResponse(['sysDescr.0' => 'No Such Object available on this agent at this OID']);
        $this->assertFalse($response->isValid());
        $this->assertSame('No Such Object available on this agent at this OID', $response->getErrorMessage());
        $this->assertSame([], $response->values());

        // No more variables left
        $response = new SnmpResponse(['iso.9' => 'No more variables left in this MIB View (It is past the end of the MIB tree)']);
        $this->assertFalse($response->isValid());
        $this->assertSame('No more variables left in this MIB View (It is past the end of the MIB tree)', $response->getErrorMessage());
        $this->assertSame([], $response->values());

        // Partial response (mixed valid and bad values)
        $response = new SnmpResponse([
            'IF-MIB::ifDescr[1]' => 'lo',
            'IF-MIB::ifDescr[2]' => 'No Such Instance currently exists at this OID',
        ]);
        $this->assertFalse($response->isValid());
        $this->assertTrue($response->isValid(ignore_partial: true));
        $this->assertSame(['IF-MIB::ifDescr[1]' => 'lo'], $response->values());

        // Filter markers like End of MIB and = NULL do not invalidate response
        $response = new SnmpResponse([
            'IF-MIB::ifDescr[1]' => 'lo',
            'IF-MIB::ifDescr[2]' => 'End of MIB',
            'IF-MIB::ifDescr[3]' => ' = NULL',
        ]);
        $this->assertTrue($response->isValid());
        $this->assertSame(['IF-MIB::ifDescr[1]' => 'lo'], $response->values());

        // Multiple errors: captures first error
        $response = new SnmpResponse([
            'sysDescr.0' => 'No Such Instance currently exists at this OID',
            'sysName.0' => 'No Such Object available on this agent at this OID',
        ]);
        $this->assertFalse($response->isValid());
        $this->assertSame('No Such Instance currently exists at this OID', $response->getErrorMessage());
    }

    public function testAppendWithPrepopulatedValues(): void
    {
        $first = new SnmpResponse(['IF-MIB::ifDescr[1]' => 'lo']);
        $second = new SnmpResponse(['IF-MIB::ifDescr[2]' => 'enp4s0']);

        $combined = $first->append($second);

        $this->assertTrue($combined->isValid());
        $this->assertSame([
            'IF-MIB::ifDescr[1]' => 'lo',
            'IF-MIB::ifDescr[2]' => 'enp4s0',
        ], $combined->values());
    }

    public function testRawOutput(): void
    {
        $response = new SnmpResponse([
            'IF-MIB::ifDescr[1]' => 'lo',
            'IF-MIB::ifDescr[2]' => 'enp4s0',
        ], '', 0, []);
        $this->assertSame("IF-MIB::ifDescr[1] = lo\nIF-MIB::ifDescr[2] = enp4s0\n", $response->raw());
        $this->assertSame("IF-MIB::ifDescr[1] = lo\nIF-MIB::ifDescr[2] = enp4s0\n", (string) $response);

        $rawResponse = new RawSnmpResponse("IF-MIB::ifDescr[1] = lo\n");
        $this->assertSame("IF-MIB::ifDescr[1] = lo\n", $rawResponse->raw());
        $this->assertSame("IF-MIB::ifDescr[1] = lo\n", (string) $rawResponse);
    }
}
