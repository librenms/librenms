<?php
/**
 * SnmpTranslateTest.php
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit;

use App\Models\Device;
use LibreNMS\Tests\TestCase;

class SnmpTranslateTest extends TestCase
{
    public function testSimpleInput(): void
    {
        $actual = \SnmpQuery::numeric()->translate('IF-MIB::ifTable');
        $this->assertEquals('.1.3.6.1.2.1.2.2', $actual);

        $actual = \SnmpQuery::numeric()->translate('ifTable', 'IF-MIB');
        $this->assertEquals('.1.3.6.1.2.1.2.2', $actual);

        $actual = \SnmpQuery::numeric()->translate('ifTable', 'ALL');
        $this->assertEquals('.1.3.6.1.2.1.2.2', $actual);

        $actual = \SnmpQuery::translate('IF-MIB::ifTable');
        $this->assertEquals('IF-MIB::ifTable', $actual);

        $actual = \SnmpQuery::numeric()->translate('.1.3.6.1.2.1.2.2');
        $this->assertEquals('.1.3.6.1.2.1.2.2', $actual);

        $actual = \SnmpQuery::translate('.1.3.6.1.2.1.2.2', 'IF-MIB');
        $this->assertEquals('IF-MIB::ifTable', $actual);

        $actual = \SnmpQuery::numeric()->translate('1.3.6.1.2.1.2.2');
        $this->assertEquals('.1.3.6.1.2.1.2.2', $actual);

        $actual = \SnmpQuery::translate('.1.3.6.1.2.1.2.2', 'ALL');
        $this->assertEquals('RFC1213-MIB::ifTable', $actual);

        $actual = \SnmpQuery::numeric()->translate('ifTable', 'IP-MIB');
        $this->assertEquals('.1.3.6.1.2.1.2.2', $actual);

        $actual = \SnmpQuery::translate('ifTable', 'IP-MIB');
        $this->assertEquals('IF-MIB::ifTable', $actual);

        // with index
        $actual = \SnmpQuery::numeric()->translate('IF-MIB::ifTable.0');
        $this->assertEquals('.1.3.6.1.2.1.2.2.0', $actual);

        $actual = \SnmpQuery::numeric()->translate('ifTable.0', 'IF-MIB');
        $this->assertEquals('.1.3.6.1.2.1.2.2.0', $actual);

        $actual = \SnmpQuery::numeric()->translate('ifTable.0', 'ALL');
        $this->assertEquals('.1.3.6.1.2.1.2.2.0', $actual);

        $actual = \SnmpQuery::translate('IF-MIB::ifTable.0');
        $this->assertEquals('IF-MIB::ifTable.0', $actual);

        $actual = \SnmpQuery::numeric()->translate('.1.3.6.1.2.1.2.2.0');
        $this->assertEquals('.1.3.6.1.2.1.2.2.0', $actual);

        $actual = \SnmpQuery::translate('.1.3.6.1.2.1.2.2.0', 'IF-MIB');
        $this->assertEquals('IF-MIB::ifTable.0', $actual);

        $actual = \SnmpQuery::numeric()->translate('1.3.6.1.2.1.2.2.0');
        $this->assertEquals('.1.3.6.1.2.1.2.2.0', $actual);

        $actual = \SnmpQuery::translate('.1.3.6.1.2.1.2.2.0', 'ALL');
        $this->assertEquals('RFC1213-MIB::ifTable.0', $actual);

        $actual = \SnmpQuery::translate('ifTable.0', 'IP-MIB');
        $this->assertEquals('IF-MIB::ifTable.0', $actual);

        $actual = \SnmpQuery::translate('iso.3.6.1.2.1.1.1.0', 'SNMPv2-MIB');
        $this->assertEquals('SNMPv2-MIB::sysDescr.0', $actual);

        $actual = \SnmpQuery::numeric()->translate('iso.3.6.1.2.1.1.1.0', 'SNMPv2-MIB');
        $this->assertEquals('.1.3.6.1.2.1.1.1.0', $actual);
    }

    public function testFailedInput(): void
    {
        $actual = \SnmpQuery::numeric()->translate('ifTable');
        $this->assertEquals('', $actual);

        $actual = \SnmpQuery::translate('ifTable');
        $this->assertEquals('', $actual);

        $actual = \SnmpQuery::numeric()->translate('ifTable', 'ASDF-MIB:SNMPv2-MIB');
        $this->assertEquals('', $actual);

        $actual = \SnmpQuery::translate('ifTable');
        $this->assertEquals('', $actual);

        $actual = \SnmpQuery::numeric()->translate('ifTable');
        $this->assertEquals('', $actual);

        $actual = \SnmpQuery::translate('ifTable');
        $this->assertEquals('', $actual);
    }

    public function testComplexInput(): void
    {
        $actual = \SnmpQuery::translate('.1.3.6.1.2.1.2.2', 'RFC1213-MIB:IF-MIB');
        $this->assertEquals('RFC1213-MIB::ifTable', $actual);

        $actual = \SnmpQuery::translate('.1.3.6.1.2.1.2.2', 'IF-MIB:RFC1213-MIB');
        $this->assertEquals('IF-MIB::ifTable', $actual);

        $actual = \SnmpQuery::translate('ifTable', 'RFC1213-MIB:IF-MIB');
        $this->assertEquals('RFC1213-MIB::ifTable', $actual);

        $actual = \SnmpQuery::translate('ifTable', 'IF-MIB:RFC1213-MIB');
        $this->assertEquals('IF-MIB::ifTable', $actual);

        // partial numeric
        $device = Device::factory()->make(['os' => 'dlink']);
        $actual = \SnmpQuery::device($device)->numeric()->translate('.1.3.6.1.4.1.171.14.5.1.4.1.4.1.dram', 'EQUIPMENT-MIB:DLINKSW-ENTITY-EXT-MIB');
        $this->assertEquals('.1.3.6.1.4.1.171.14.5.1.4.1.4.1.1', $actual);

        $actual = \SnmpQuery::device($device)->numeric()->translate('iso.3.6.1.4.1.171.14.5.1.4.1.4.1.dram', 'EQUIPMENT-MIB:DLINKSW-ENTITY-EXT-MIB');
        $this->assertEquals('.1.3.6.1.4.1.171.14.5.1.4.1.4.1.1', $actual);
    }
}
