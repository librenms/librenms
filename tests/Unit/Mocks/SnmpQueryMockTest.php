<?php

/**
 * SnmpQueryMockTest.php
 *
 * Regression tests for the OID prefix matching and output format bugs
 * in tests/Mocks/SnmpQueryMock.php. Each test asserts behavior that was
 * broken before the fix and is correct after.
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
 * @copyright  2026 Josh Thomas-Ward
 * @author     Josh Thomas-Ward <josh.thomasward@pelagicai.com>
 */

namespace LibreNMS\Tests\Unit\Mocks;

use App\Models\Device;
use DeviceCache;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;
use LibreNMS\Tests\Mocks\SnmpQueryMock;

final class SnmpQueryMockTest extends DBTestCase
{
    use DatabaseTransactions;

    private const FIXTURE = 'snmpquerymock_regression';
    private const BASE_OID = '1.3.6.1.2.1.2.2.1.2';

    private function makeMock(): SnmpQueryMock
    {
        $device = Device::factory()->create(['community' => self::FIXTURE]);
        DeviceCache::setPrimary($device->device_id);

        return (new SnmpQueryMock())->numeric();
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
        // having distinct .1, .2, .3 suffixes.
        $this->assertStringContainsString('1.3.6.1.2.1.2.2.1.2.1 = eth0', $output);
        $this->assertStringContainsString('1.3.6.1.2.1.2.2.1.2.2 = eth1', $output);
        $this->assertStringContainsString('1.3.6.1.2.1.2.2.1.2.3 = eth2', $output);
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
}
