<?php

/**
 * SnmprecSnmpBackendTest.php
 *
 * Regression and unit tests for tests/Mocks/SnmprecSnmpBackend.php:
 * prefix-overlap matching, full OID index suffix in walk output,
 * newline termination, array-based SnmpResponse creation, and numeric output
 * parity with real net-snmp.
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

use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Enum\SnmpOidOutput;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Tests\Mocks\SnmprecSnmpBackend;
use LibreNMS\Tests\SnmpsimHelpers;
use LibreNMS\Tests\TestCase;

final class SnmprecSnmpBackendTest extends TestCase
{
    use SnmpsimHelpers;

    private const FIXTURE = 'snmpquerymock_regression';
    private const BASE_OID = '1.3.6.1.2.1.2.2.1.2';

    private function makeBackend(): SnmprecSnmpBackend
    {
        return new SnmprecSnmpBackend();
    }

    private function makeConfig(): SnmpConfig
    {
        return new SnmpConfig(community: self::FIXTURE);
    }

    private function makeNumericOptions(): SnmpQueryOptions
    {
        $options = SnmpQueryOptions::quickPrint();
        $options->oidFormat = SnmpOidOutput::Numeric;

        return $options;
    }

    public function test_walk_does_not_match_numerically_adjacent_subtrees(): void
    {
        $values = $this->makeBackend()
            ->walk('localhost', self::BASE_OID, $this->makeConfig(), $this->makeNumericOptions())
            ->values();

        // base OID is "1.3.6.1.2.1.2.2.1.2"; siblings "1.3.6.1.2.1.2.20.x" and
        // "1.3.6.1.2.1.2.21.x" share the numeric prefix without a dot boundary.
        $this->assertArrayNotHasKey('.1.3.6.1.2.1.2.20.1', $values);
        $this->assertArrayNotHasKey('.1.3.6.1.2.1.2.21.1', $values);
        $this->assertNotContains('sibling-subtree-20', $values);
        $this->assertNotContains('sibling-subtree-21', $values);
    }

    public function test_walk_returns_each_row_with_full_oid_suffix(): void
    {
        $values = $this->makeBackend()
            ->walk('localhost', self::BASE_OID, $this->makeConfig(), $this->makeNumericOptions())
            ->values();

        $this->assertSame([
            '.1.3.6.1.2.1.2.2.1.2.1' => 'eth0',
            '.1.3.6.1.2.1.2.2.1.2.2' => 'eth1',
            '.1.3.6.1.2.1.2.2.1.2.3' => 'eth2',
        ], $values);
    }

    public function test_direct_backend_get_and_walk(): void
    {
        $backend = $this->makeBackend();
        $config = $this->makeConfig();
        $options = $this->makeNumericOptions();

        $walkResponse = $backend->walk('localhost', self::BASE_OID, $config, $options);
        $this->assertTrue($walkResponse->isValid());
        $this->assertSame([
            '.1.3.6.1.2.1.2.2.1.2.1' => 'eth0',
            '.1.3.6.1.2.1.2.2.1.2.2' => 'eth1',
            '.1.3.6.1.2.1.2.2.1.2.3' => 'eth2',
        ], $walkResponse->values());

        $getResponse = $backend->get('localhost', [self::BASE_OID . '.1', self::BASE_OID . '.2'], $config, $options);
        $this->assertTrue($getResponse->isValid());
        $this->assertSame([
            '.1.3.6.1.2.1.2.2.1.2.1' => 'eth0',
            '.1.3.6.1.2.1.2.2.1.2.2' => 'eth1',
        ], $getResponse->values());
    }

    public function test_numeric_output_matches_real_net_snmp(): void
    {
        $this->requireSnmpsim();

        $mock = $this->makeBackend();
        $real = resolve(SnmpBackendInterface::class);

        $target = $this->getSnmpsimIp() ?? '127.0.0.1';
        $config = new SnmpConfig(
            community: self::FIXTURE,
            port: $this->getSnmpsimPort(),
            timeout: 3,
            retries: 0,
        );
        $options = $this->makeNumericOptions();

        $this->assertSame(
            $real->walk($target, self::BASE_OID, $config, $options)->values(),
            $mock->walk($target, self::BASE_OID, $config, $options)->values(),
            'mock walk output diverges from real net-snmp'
        );
        $this->assertSame(
            $real->get($target, [self::BASE_OID . '.1'], $config, $options)->values(),
            $mock->get($target, [self::BASE_OID . '.1'], $config, $options)->values(),
            'mock get output diverges from real net-snmp'
        );
        $this->assertSame(
            $real->get($target, [self::BASE_OID . '.99'], $config, $options)->values(),
            $mock->get($target, [self::BASE_OID . '.99'], $config, $options)->values(),
            'mock get output for a missing OID diverges from real net-snmp'
        );
        $this->assertSame(
            $real->next($target, [self::BASE_OID], $config, $options)->values(),
            $mock->next($target, [self::BASE_OID], $config, $options)->values(),
            'mock getnext output diverges from real net-snmp'
        );
    }
}
