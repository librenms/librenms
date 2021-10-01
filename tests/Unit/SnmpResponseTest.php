<?php
/*
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit;

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
    }

    public function testMultiLine(): void
    {
        $response = new SnmpResponse("SNMPv2-MIB::sysDescr.0 = \"something\n on two lines\"\n");

        $this->assertTrue($response->isValid());
        $this->assertEquals("\"something\n on two lines\"", $response->value());
        $this->assertEquals(['SNMPv2-MIB::sysDescr.0' => "\"something\n on two lines\""], $response->values());
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
    }
}
