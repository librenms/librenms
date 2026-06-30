<?php

/**
 * SnmpQueryMockTest.php
 *
 * Regression tests for bugs in tests/Mocks/SnmpQueryMock.php:
 * prefix-overlap matching, dropped OID index suffix in walk output,
 * missing newline termination, and numeric output diverging from real
 * net-snmp (missing leading dot, missing No Such Instance line). When
 * SNMPSIM is set, numeric mock output is asserted byte-identical to a
 * real NetSnmpQuery against the same fixture.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Josh Thomas-Ward
 * @author     Josh Thomas-Ward <josh.thomasward@pelagicai.com>
 */

namespace LibreNMS\Tests\Unit\Mocks;

use App\Facades\DeviceCache;
use App\Models\Device;
use Illuminate\Database\Eloquent\Collection;
use LibreNMS\Data\Source\NetSnmpQuery;
use LibreNMS\Tests\Mocks\SnmpQueryMock;
use LibreNMS\Tests\SnmpsimHelpers;
use LibreNMS\Tests\TestCase;

final class SnmpQueryMockTest extends TestCase
{
    use SnmpsimHelpers;

    private const FIXTURE = 'snmpquerymock_regression';
    private const BASE_OID = '1.3.6.1.2.1.2.2.1.2';

    private function makeMock(): SnmpQueryMock
    {
        // SnmpQueryMock reads the community from DeviceCache::getPrimary() to
        // pick its snmprec fixture. Fake the primary device so the mock has a
        // community without touching the database.
        $device = new Device(['community' => self::FIXTURE]);
        $device->device_id = 1;
        DeviceCache::fake($device);
        DeviceCache::setPrimary($device->device_id);

        $mock = new SnmpQueryMock();
        $mock->numeric();

        return $mock;
    }

    public function test_walk_does_not_match_numerically_adjacent_subtrees(): void
    {
        $output = $this->makeMock()->walk(self::BASE_OID)->raw;

        // base OID is "1.3.6.1.2.1.2.2.1.2"; siblings "1.3.6.1.2.1.2.20.x" and
        // "1.3.6.1.2.1.2.21.x" share the numeric prefix without a dot boundary.
        // Before the fix, Str::startsWith($key, $num_oid) matched them.
        $this->assertStringNotContainsString('sibling-subtree-20', $output);
        $this->assertStringNotContainsString('sibling-subtree-21', $output);
    }

    public function test_walk_returns_each_row_with_full_oid_suffix(): void
    {
        $output = $this->makeMock()->walk(self::BASE_OID)->raw;

        // Before the fix, every row was keyed with the base OID, so all three
        // ifDescr rows appeared as "1.3.6.1.2.1.2.2.1.2 = ..." instead of
        // having distinct .1, .2, .3 suffixes. Byte-exact net-snmp -OQXUte -Pu
        // -On output, verified against snmpsim (see the parity test below).
        $this->assertSame(
            ".1.3.6.1.2.1.2.2.1.2.1 = eth0\n.1.3.6.1.2.1.2.2.1.2.2 = eth1\n.1.3.6.1.2.1.2.2.1.2.3 = eth2\n",
            $output
        );
    }

    public function test_walk_output_lines_are_newline_terminated(): void
    {
        $output = $this->makeMock()->walk(self::BASE_OID)->raw;

        // Real net-snmp terminates every line with \n; the mock used to omit
        // them, which made multi-row numeric walks come back as one
        // concatenated unparseable string.
        $lines = array_filter(explode("\n", $output), fn ($l) => $l !== '');
        $this->assertCount(3, $lines, "expected exactly 3 newline-separated rows, got: $output");
        $this->assertStringEndsWith("\n", $output);
    }

    public function test_numeric_output_matches_real_net_snmp(): void
    {
        $this->requireSnmpsim();

        $mock = $this->makeMock();
        $real = NetSnmpQuery::make()->device($this->snmpsimDevice())->numeric();

        $this->assertSame(
            $real->walk(self::BASE_OID)->raw,
            $mock->walk(self::BASE_OID)->raw,
            'mock walk output diverges from real net-snmp'
        );
        $this->assertSame(
            $real->get(self::BASE_OID . '.1')->raw,
            $mock->get(self::BASE_OID . '.1')->raw,
            'mock get output diverges from real net-snmp'
        );
        $this->assertSame(
            $real->get(self::BASE_OID . '.99')->raw,
            $mock->get(self::BASE_OID . '.99')->raw,
            'mock get output for a missing OID diverges from real net-snmp'
        );
        $this->assertSame(
            $real->next(self::BASE_OID)->raw,
            $mock->next(self::BASE_OID)->raw,
            'mock getnext output diverges from real net-snmp'
        );
    }

    private function snmpsimDevice(): Device
    {
        $device = new Device([
            'hostname' => $this->getSnmpsimIp(),
            'port' => $this->getSnmpsimPort(),
            'snmpver' => 'v2c',
            'community' => self::FIXTURE,
            'timeout' => 3,
            'retries' => 0,
            'os' => 'generic',
        ]);
        $device->setRelation('attribs', new Collection); // getAttrib without a database

        return $device;
    }
}
