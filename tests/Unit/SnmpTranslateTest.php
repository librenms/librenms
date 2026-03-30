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
use LibreNMS\Data\Sources\NetSnmpTranslate;
use LibreNMS\Tests\TestCase;

final class SnmpTranslateTest extends TestCase
{
    public function testSimpleInput(): void
    {
        $actual = NetSnmpTranslate::make()->numeric()->translate('IF-MIB::ifTable');
        $this->assertEquals('.1.3.6.1.2.1.2.2', $actual);

        $actual = NetSnmpTranslate::make()->numeric()->mibs(['IF-MIB'], append: false)->translate('ifTable');
        $this->assertEquals('.1.3.6.1.2.1.2.2', $actual);

        $actual = NetSnmpTranslate::make()->numeric()->mibs(['ALL'], append: false)->translate('ifTable');
        $this->assertEquals('.1.3.6.1.2.1.2.2', $actual);

        $actual = NetSnmpTranslate::make()->translate('IF-MIB::ifTable');
        $this->assertEquals('IF-MIB::ifTable', $actual);

        $actual = NetSnmpTranslate::make()->numeric()->translate('.1.3.6.1.2.1.2.2');
        $this->assertEquals('.1.3.6.1.2.1.2.2', $actual);

        $actual = NetSnmpTranslate::make()->mibs(['IF-MIB'])->translate('.1.3.6.1.2.1.2.2');
        $this->assertEquals('IF-MIB::ifTable', $actual);

        $actual = NetSnmpTranslate::make()->numeric()->translate('1.3.6.1.2.1.2.2');
        $this->assertEquals('.1.3.6.1.2.1.2.2', $actual);

        $actual = NetSnmpTranslate::make()->mibs(['ALL'], append: false)->translate('.1.3.6.1.2.1.2.2');
        $this->assertEquals('RFC1213-MIB::ifTable', $actual);

        $actual = NetSnmpTranslate::make()->numeric()->mibs(['IP-MIB'])->translate('ifTable');
        $this->assertEquals('.1.3.6.1.2.1.2.2', $actual);

        $actual = NetSnmpTranslate::make()->mibs(['IP-MIB'])->translate('ifTable');
        $this->assertEquals('IF-MIB::ifTable', $actual);

        // with index
        $actual = NetSnmpTranslate::make()->numeric()->translate('IF-MIB::ifTable.0');
        $this->assertEquals('.1.3.6.1.2.1.2.2.0', $actual);

        $actual = NetSnmpTranslate::make()->numeric()->mibs(['IF-MIB'])->translate('ifTable.0');
        $this->assertEquals('.1.3.6.1.2.1.2.2.0', $actual);

        $actual = NetSnmpTranslate::make()->numeric()->mibs(['ALL'], append: false)->translate('ifTable.0');
        $this->assertEquals('.1.3.6.1.2.1.2.2.0', $actual);

        $actual = NetSnmpTranslate::make()->translate('IF-MIB::ifTable.0');
        $this->assertEquals('IF-MIB::ifTable.0', $actual);

        $actual = NetSnmpTranslate::make()->numeric()->translate('.1.3.6.1.2.1.2.2.0');
        $this->assertEquals('.1.3.6.1.2.1.2.2.0', $actual);

        $actual = NetSnmpTranslate::make()->mibs(['IF-MIB'])->translate('.1.3.6.1.2.1.2.2.0');
        $this->assertEquals('IF-MIB::ifTable.0', $actual);

        $actual = NetSnmpTranslate::make()->numeric()->translate('1.3.6.1.2.1.2.2.0');
        $this->assertEquals('.1.3.6.1.2.1.2.2.0', $actual);

        $actual = NetSnmpTranslate::make()->mibs(['ALL'], append: false)->translate('.1.3.6.1.2.1.2.2.0');
        $this->assertEquals('RFC1213-MIB::ifTable.0', $actual);

        $actual = NetSnmpTranslate::make()->mibs(['IP-MIB'])->translate('ifTable.0');
        $this->assertEquals('IF-MIB::ifTable.0', $actual);

        $actual = NetSnmpTranslate::make()->mibs(['SNMPv2-MIB'])->translate('iso.3.6.1.2.1.1.1.0');
        $this->assertEquals('SNMPv2-MIB::sysDescr.0', $actual);

        $actual = NetSnmpTranslate::make()->numeric()->mibs(['SNMPv2-MIB'])->translate('iso.3.6.1.2.1.1.1.0');
        $this->assertEquals('.1.3.6.1.2.1.1.1.0', $actual);
    }

    public function testFailedInput(): void
    {
        $actual = NetSnmpTranslate::make()->translate('ifTable');
        $this->assertEquals('IF-MIB::ifTable', $actual);

        $actual = NetSnmpTranslate::make()->numeric()->mibs([], append: false)->translate('ifTable');
        $this->assertEquals('', $actual);

        $actual = NetSnmpTranslate::make()->mibs([], append: false)->translate('ifTable');
        $this->assertEquals('', $actual);

        $actual = NetSnmpTranslate::make()->numeric()->mibs(['ASDF-MIB', 'SNMPv2-MIB'], append: false)->translate('ifTable');
        $this->assertEquals('', $actual);

        $actual = NetSnmpTranslate::make()->mibs([], append: false)->translate('ifTable');
        $this->assertEquals('', $actual);

        $actual = NetSnmpTranslate::make()->numeric()->mibs([], append: false)->translate('ifTable');
        $this->assertEquals('', $actual);

        $actual = NetSnmpTranslate::make()->mibs([], append: false)->translate('ifTable');
        $this->assertEquals('', $actual);
    }

    public function testComplexInput(): void
    {
        $actual = NetSnmpTranslate::make()->mibs(['RFC1213-MIB', 'IF-MIB'], append: false)->translate('.1.3.6.1.2.1.2.2');
        $this->assertEquals('RFC1213-MIB::ifTable', $actual);

        $actual = NetSnmpTranslate::make()->mibs(['IF-MIB', 'RFC1213-MIB'], append: false)->translate('.1.3.6.1.2.1.2.2');
        $this->assertEquals('IF-MIB::ifTable', $actual);

        $actual = NetSnmpTranslate::make()->translate('ifTable');
        $this->assertEquals('IF-MIB::ifTable', $actual);

        $actual = NetSnmpTranslate::make()->mibs(['RFC1213-MIB', 'IF-MIB'], append: false)->translate('ifTable');
        $this->assertEquals('RFC1213-MIB::ifTable', $actual);

        $actual = NetSnmpTranslate::make()->mibs(['IF-MIB', 'RFC1213-MIB'], append: false)->translate('ifTable');
        $this->assertEquals('IF-MIB::ifTable', $actual);

        // partial numeric
        $device = Device::factory()->make(['os' => 'dlink']);
        $actual = NetSnmpTranslate::make()->device($device)->numeric()->mibs(['EQUIPMENT-MIB', 'DLINKSW-ENTITY-EXT-MIB'], append: false)->translate('.1.3.6.1.4.1.171.14.5.1.4.1.4.1.dram');
        $this->assertEquals('.1.3.6.1.4.1.171.14.5.1.4.1.4.1.1', $actual);

        $actual = NetSnmpTranslate::make()->device($device)->numeric()->mibs(['EQUIPMENT-MIB', 'DLINKSW-ENTITY-EXT-MIB'], append: false)->translate('iso.3.6.1.4.1.171.14.5.1.4.1.4.1.dram');
        $this->assertEquals('.1.3.6.1.4.1.171.14.5.1.4.1.4.1.1', $actual);
    }
}
