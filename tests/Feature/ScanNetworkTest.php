<?php

/**
 * ScanNetworkTest.php
 *
 * Tests for lnms scan:network command
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

namespace LibreNMS\Tests\Feature;

use App\Facades\LibrenmsConfig;
use App\Models\Secret;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Enum\SecretType;
use LibreNMS\Scan\ScanOptions;
use LibreNMS\Tests\DBTestCase;
use PHPUnit\Framework\Attributes\TestDox;

#[TestDox('Scan Network CLI')]
final class ScanNetworkTest extends DBTestCase
{
    use DatabaseTransactions;

    #[TestDox('Error when no network provided and nets config is empty')]
    public function testNoNetworkError(): void
    {
        LibrenmsConfig::set('nets', []);

        $this->artisan('scan:network')
            ->assertExitCode(1)
            ->expectsOutput(__('commands.scan:network.errors.no_network'))
            ->execute();
    }

    #[TestDox('Error when invalid network provided')]
    public function testInvalidNetworkError(): void
    {
        $this->artisan('scan:network', ['network' => ['not-an-ip']])
            ->assertExitCode(2)
            ->expectsOutput(__('commands.scan:network.errors.invalid_network', ['network' => 'not-an-ip']))
            ->execute();
    }

    #[TestDox('Scan single IP network')]
    public function testSingleIpScan(): void
    {
        $this->artisan('scan:network', ['network' => ['127.0.0.1/32']])
            ->assertExitCode(0)
            ->execute();
    }

    #[TestDox('Scan using alias scan')]
    public function testAliasScan(): void
    {
        $this->artisan('scan', ['network' => ['127.0.0.1/32']])
            ->assertExitCode(0)
            ->execute();
    }

    #[TestDox('Scan with legend option')]
    public function testLegendOption(): void
    {
        $this->artisan('scan:network', ['network' => ['127.0.0.1/32'], '--legend' => true])
            ->assertExitCode(0)
            ->expectsOutputToContain('Legend:')
            ->execute();
    }

    #[TestDox('Scan respects excluded networks config')]
    public function testExcludedNetwork(): void
    {
        LibrenmsConfig::set('autodiscovery.nets-exclude', ['127.0.0.1/32']);

        $this->artisan('scan:network', ['network' => ['127.0.0.1/32']])
            ->assertExitCode(0)
            ->expectsOutputToContain('1 ip excluded by config')
            ->execute();
    }

    #[TestDox('Scan probes with the default SNMP credentials in order')]
    public function testDefaultSnmpConfigs(): void
    {
        $v3 = Secret::factory()->create([
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v3', 'authlevel' => 'noAuthNoPriv', 'authname' => 'scanner'],
        ]);
        $v2c = Secret::factory()->create([
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'scan-community'],
        ]);
        Secret::factory()->create([
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'not-a-default'],
        ]);
        LibrenmsConfig::set('snmp.default_credentials', [$v2c->id, $v3->id]);

        $configs = ScanOptions::defaultSnmpConfigs();

        $this->assertSame([$v2c->id, $v3->id], array_keys($configs), 'keyed by secret id, in order');
        $this->assertSame('scan-community', $configs[$v2c->id]->community);
        $this->assertSame('scanner', $configs[$v3->id]->authname);
        $this->assertSame(1, $configs[$v2c->id]->timeout);
        $this->assertSame(0, $configs[$v2c->id]->retries);
    }

    #[TestDox('Scan warns when there are no default SNMP credentials')]
    public function testNoSnmpCredentialsWarning(): void
    {
        LibrenmsConfig::set('snmp.default_credentials', []);

        $this->artisan('scan:network', ['network' => ['127.0.0.1/32']])
            ->assertExitCode(0)
            ->expectsOutputToContain(__('commands.scan:network.errors.no_snmp_credentials'))
            ->execute();
    }

    #[TestDox('-P is the deprecated ping fallback option')]
    public function testDeprecatedPingOption(): void
    {
        $this->artisan('scan:network', ['network' => ['127.0.0.1/32'], '-P' => true])
            ->assertExitCode(0)
            ->expectsOutputToContain(__('commands.scan:network.errors.ping_deprecated'))
            ->execute();
    }
}
