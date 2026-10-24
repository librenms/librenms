<?php

/**
 * SnmpConnectionErrorsTest.php
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit;

use App\Events\SnmpQueryExecuted;
use LibreNMS\Data\Source\Snmp\SnmpConnectionErrors;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Tests\TestCase;

final class SnmpConnectionErrorsTest extends TestCase
{
    public function testCollectsErrorsOfTheCallbackOnly(): void
    {
        $this->query('BEFORE-MIB::before', "Timeout: No Response from udp:192.0.2.1:161\n");

        [$result, $errors] = SnmpConnectionErrors::during(function () {
            $this->query('TEST-MIB::ok', '');
            $this->query('TEST-MIB::timeout', "Timeout: No Response from udp:192.0.2.1:161\n");

            return 'result';
        });

        $this->assertSame('result', $result);
        $this->assertSame(['TEST-MIB::timeout: Timeout: No Response from udp:192.0.2.1:161'], $errors);
    }

    public function testNestedCallbacks(): void
    {
        [, $outer] = SnmpConnectionErrors::during(function (): void {
            [, $inner] = SnmpConnectionErrors::during(fn () => $this->query('TEST-MIB::inner', 'Authentication failure'));
            $this->assertCount(1, $inner);
        });

        $this->assertSame(['TEST-MIB::inner: Authentication failure'], $outer, 'Errors of nested callbacks count for the outer callback');
    }

    private function query(string $oid, string $stderr): void
    {
        event(new SnmpQueryExecuted(
            target: 'udp:192.0.2.1:161',
            method: 'snmpbulkwalk',
            oids: [$oid],
            duration: 0.1,
            response: new SnmpResponse([], $stderr, $stderr ? 1 : 0),
            options: new SnmpQueryOptions,
            config: new SnmpConfig('v2c', 'public', null, null, null, null, null, null, 'udp', 161, null, 1, 5, 10, 10, true, 'ifIndex'),
            backend: 'NetSnmp',
        ));
    }
}
