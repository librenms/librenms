<?php

namespace LibreNMS\Tests\Feature\Scan;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Secret;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Enum\SecretType;
use LibreNMS\Scan\ScanOptions;
use LibreNMS\Tests\DBTestCase;
use LibreNMS\Tests\Traits\FakesNetworkTools;
use PHPUnit\Framework\Attributes\TestDox;

#[TestDox('scan:network')]
final class ScanNetworkTest extends DBTestCase
{
    use DatabaseTransactions;
    use FakesNetworkTools;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeFping([]);
        LibrenmsConfig::set('snmp.default_credentials', []);
    }

    #[TestDox('Needs a network when nets is not set')]
    public function testNoNetwork(): void
    {
        LibrenmsConfig::set('nets', []);

        $this->artisan('scan:network')
            ->expectsOutput(__('commands.scan:network.errors.no_network'))
            ->assertExitCode(1);
    }

    #[TestDox('Rejects an invalid network')]
    public function testInvalidNetwork(): void
    {
        $this->artisan('scan:network', ['network' => ['not-an-ip']])
            ->expectsOutput(__('commands.scan:network.errors.invalid_network', ['network' => 'not-an-ip']))
            ->assertExitCode(2);
    }

    #[TestDox('Stops when fping is missing')]
    public function testMissingFping(): void
    {
        $this->setBinary('fping', '/nonexistent/fping');

        $this->artisan('scan:network', ['network' => ['192.0.2.1/32']])
            ->expectsOutput(__('commands.scan:network.errors.missing_binary', ['binary' => 'fping']))
            ->assertExitCode(3);
    }

    #[TestDox('Scans the nets setting, with the scan alias')]
    public function testNetsSetting(): void
    {
        LibrenmsConfig::set('nets', ['192.0.2.0/30']);

        $this->artisan('scan')
            ->expectsOutputToContain('Scanned 2 IPs: 0 known devices, added 0 devices, failed to add 0 devices')
            ->assertExitCode(0);
    }

    #[TestDox('Takes networks with the snmp-scan.py -r option')]
    public function testRangeOption(): void
    {
        $this->artisan('scan:network', ['-r' => ['192.0.2.0/30', '192.0.2.4/31']])
            ->expectsOutputToContain('Scanned 4 IPs')
            ->assertExitCode(0);
    }

    #[TestDox('Prints the legend')]
    public function testLegend(): void
    {
        $this->artisan('scan:network', ['network' => ['192.0.2.1/32'], '--legend' => true])
            ->expectsOutput(__('commands.scan:network.legend'))
            ->expectsOutput('*  ' . __('commands.scan:network.outcomes.Known'))
            ->assertExitCode(0);
    }

    #[TestDox('Excluded addresses are not counted as scanned')]
    public function testExcluded(): void
    {
        LibrenmsConfig::set('autodiscovery.nets-exclude', ['192.0.2.1/32', 'not-a-network']);

        $this->artisan('scan:network', ['network' => ['192.0.2.0/30']])
            ->expectsOutput(__('commands.scan:network.errors.invalid_excluded_network', ['network' => 'not-a-network']))
            ->expectsOutputToContain('Scanned 1 IPs')
            ->expectsOutput(trans_choice('commands.scan:network.summary_Excluded', 1))
            ->assertExitCode(0);
    }

    #[TestDox('Verbose output names each address and why')]
    public function testVerbose(): void
    {
        Device::factory()->create(['hostname' => 'known.example.com', 'ip' => '192.0.2.1']);

        $this->artisan('scan:network', ['network' => ['192.0.2.1/32'], '-v' => true])
            ->expectsOutputToContain('192.0.2.1')
            ->expectsOutput(__('exceptions.host_exists.hostname_exists', ['hostname' => 'known.example.com']))
            ->expectsOutputToContain('Scanned 1 IPs: 1 known devices')
            ->assertExitCode(0);
    }

    #[TestDox('Warns without default SNMP credentials')]
    public function testNoSnmpCredentials(): void
    {
        $this->artisan('scan:network', ['network' => ['192.0.2.1/32']])
            ->expectsOutput(__('commands.scan:network.errors.no_snmp_credentials'))
            ->assertExitCode(0);
    }

    #[TestDox('-P is the deprecated ping fallback option')]
    public function testDeprecatedPing(): void
    {
        $this->artisan('scan:network', ['network' => ['192.0.2.1/32'], '-P' => true])
            ->expectsOutput(__('commands.scan:network.errors.ping_deprecated'))
            ->assertExitCode(0);
    }

    #[TestDox('Probes with the default SNMP credentials in order')]
    public function testDefaultSnmpConfigs(): void
    {
        $v3 = Secret::factory()->create(['secret_type' => SecretType::Snmp, 'data' => ['version' => 'v3', 'authlevel' => 'noAuthNoPriv', 'authname' => 'scanner']]);
        $v2c = Secret::factory()->create(['secret_type' => SecretType::Snmp, 'data' => ['version' => 'v2c', 'community' => 'scan-community']]);
        Secret::factory()->create(['secret_type' => SecretType::Snmp, 'data' => ['version' => 'v2c', 'community' => 'not-a-default']]);
        LibrenmsConfig::set('snmp.default_credentials', [$v2c->id, $v3->id]);

        $configs = ScanOptions::defaultSnmpConfigs();

        $this->assertSame([$v2c->id, $v3->id], array_keys($configs));
        $this->assertSame('scan-community', $configs[$v2c->id]->community);
        $this->assertSame('scanner', $configs[$v3->id]->authname);
        $this->assertSame(1, $configs[$v2c->id]->timeout);
        $this->assertSame(0, $configs[$v2c->id]->retries);
        $this->assertSame([], ScanOptions::fromConfig(pingOnly: true)->snmpConfigs, 'ping only does not probe SNMP');
    }
}
