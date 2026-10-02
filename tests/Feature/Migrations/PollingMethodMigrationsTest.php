<?php

namespace LibreNMS\Tests\Feature\Migrations;

use App\Facades\LibrenmsConfig;
use Illuminate\Support\Facades\DB;
use LibreNMS\Tests\InMemoryDbTestCase;

/**
 * Legacy device fields and attribs are moved to polling methods and secrets.
 */
final class PollingMethodMigrationsTest extends InMemoryDbTestCase
{
    public function testSnmpCredentialsBecomeSecrets(): void
    {
        LibrenmsConfig::set('snmp.version', ['v2c']);
        LibrenmsConfig::set('snmp.community', ['public']);

        $this->migrateLegacyDevices([
            ['device_id' => 1, 'hostname' => 'a', 'snmpver' => 'v2c', 'community' => 'shared', 'port' => 161, 'transport' => 'udp'],
            ['device_id' => 2, 'hostname' => 'b', 'snmpver' => 'v2c', 'community' => 'shared', 'port' => 1161, 'transport' => 'tcp'],
            ['device_id' => 3, 'hostname' => 'c', 'snmpver' => 'v3', 'authlevel' => 'authPriv', 'authname' => 'user', 'authpass' => 'authpass', 'authalgo' => 'SHA', 'cryptopass' => 'cryptpass', 'cryptoalgo' => 'AES'],
        ], [
            ['device_id' => 1, 'attrib_type' => 'snmp_max_oid', 'attrib_value' => '20'],
            ['device_id' => 1, 'attrib_type' => 'snmp_bulk', 'attrib_value' => 'false'],
        ]);

        $a = $this->pollingMethod(1, 'snmp');
        $b = $this->pollingMethod(2, 'snmp');
        $c = $this->pollingMethod(3, 'snmp');

        // legacy port, transport and port association mode are kept, other values only when they were set
        $this->assertSame(['port' => 161, 'transport' => 'udp', 'max_oid' => 20, 'bulk' => false, 'port_association_mode' => 'ifIndex'], json_decode($a->settings, true));
        $this->assertSame(['port' => 1161, 'transport' => 'tcp', 'port_association_mode' => 'ifIndex'], json_decode($b->settings, true));
        $this->assertEquals(1, $a->enabled);

        // identical credentials share a secret
        $this->assertSame($a->secret_id, $b->secret_id);
        $this->assertSame('SNMP v2c (shared #1)', $this->secret($a->secret_id)->description);
        $this->assertSame(['version' => 'v2c', 'community' => 'shared'], $this->secretData($a->secret_id));

        $this->assertSame('SNMP for device c', $this->secret($c->secret_id)->description);
        $this->assertSame([
            'version' => 'v3',
            'authlevel' => 'authPriv',
            'authname' => 'user',
            'authpass' => 'authpass',
            'authalgo' => 'SHA',
            'cryptopass' => 'cryptpass',
            'cryptoalgo' => 'AES',
        ], $this->secretData($c->secret_id));

        // the configured communities become the default credentials
        $defaultIds = json_decode(DB::table('config')->where('config_name', 'snmp.default_credentials')->value('config_value'), true);
        $this->assertCount(1, $defaultIds);
        $this->assertSame(['version' => 'v2c', 'community' => 'public'], $this->secretData($defaultIds[0]));
        $this->assertSame('Default SNMP v2c #1', $this->secret($defaultIds[0])->description); // descriptions are not encrypted
    }

    public function testDefaultCredentialsKeepTheVersionPriority(): void
    {
        LibrenmsConfig::set('snmp.version', ['v3', 'v2c']);
        LibrenmsConfig::set('snmp.community', ['public']);
        LibrenmsConfig::set('snmp.v3', [['authlevel' => 'authPriv', 'authname' => 'preferred', 'authpass' => 'authpass', 'authalgo' => 'SHA', 'cryptopass' => 'cryptopass', 'cryptoalgo' => 'AES']]);

        $this->migrateLegacyDevices([]);

        $defaultIds = json_decode(DB::table('config')->where('config_name', 'snmp.default_credentials')->value('config_value'), true);
        $this->assertSame(['v3', 'v2c'], array_map(fn (int $id) => $this->secretData($id)['version'], $defaultIds));
        $this->assertSame('preferred', $this->secretData($defaultIds[0])['authname']);
    }

    public function testPingOnlyDevicesGetNoSnmpMethod(): void
    {
        $this->migrateLegacyDevices([
            ['device_id' => 1, 'hostname' => 'ping-only', 'snmpver' => 'v2c', 'community' => 'old-community', 'snmp_disable' => 1],
        ]);

        $this->assertNull($this->pollingMethod(1, 'snmp'));
        $this->assertNotNull($this->pollingMethod(1, 'icmp'));
        $this->assertFalse(DB::table('secrets')->where('description', 'SNMP for device ping-only')->exists());

        // rolling back the removed device fields keeps it ping only
        $this->artisan('migrate:rollback', [
            '--database' => $this->connection,
            '--path' => 'database/migrations/2026_09_22_150000_remove_obsolete_device_fields.php',
        ]);
        $this->assertEquals(1, DB::table('devices')->where('device_id', 1)->value('snmp_disable'));
    }

    public function testLegacyUnsetSnmpValuesUseTheDefaults(): void
    {
        $this->migrateLegacyDevices([
            ['device_id' => 1, 'hostname' => 'a', 'snmpver' => 'v2c', 'community' => 'shared', 'timeout' => 0, 'retries' => 0],
        ], [
            ['device_id' => 1, 'attrib_type' => 'snmp_max_oid', 'attrib_value' => '0'],
            ['device_id' => 1, 'attrib_type' => 'snmp_max_repeaters', 'attrib_value' => '0'],
        ]);

        // legacy polling ignored a timeout <= 0 and empty max oid/repeaters, but 0 retries is valid
        $this->assertSame(['port' => 161, 'transport' => 'udp', 'retries' => 0, 'port_association_mode' => 'ifIndex'], json_decode($this->pollingMethod(1, 'snmp')->settings, true));
    }

    public function testLastCheckComesFromTheStatusReason(): void
    {
        $this->migrateLegacyDevices([
            ['device_id' => 1, 'hostname' => 'up', 'status' => 1, 'status_reason' => ''],
            ['device_id' => 2, 'hostname' => 'snmp-down', 'status' => 0, 'status_reason' => 'snmp'],
            ['device_id' => 3, 'hostname' => 'icmp-down', 'status' => 0, 'status_reason' => 'icmp'],
            ['device_id' => 4, 'hostname' => 'both-down', 'status' => 0, 'status_reason' => 'icmp,snmp'],
            ['device_id' => 5, 'hostname' => 'down-unknown', 'status' => 0, 'status_reason' => ''],
        ]);

        $lastChecks = fn (int $deviceId): array => [
            'icmp' => (bool) $this->pollingMethod($deviceId, 'icmp')->last_check_successful,
            'snmp' => (bool) $this->pollingMethod($deviceId, 'snmp')->last_check_successful,
        ];

        $this->assertSame(['icmp' => true, 'snmp' => true], $lastChecks(1));
        $this->assertSame(['icmp' => true, 'snmp' => false], $lastChecks(2));
        $this->assertSame(['icmp' => false, 'snmp' => true], $lastChecks(3));
        $this->assertSame(['icmp' => false, 'snmp' => false], $lastChecks(4));
        $this->assertSame(['icmp' => false, 'snmp' => false], $lastChecks(5)); // the failed method is unknown, so it stays down
    }

    public function testIcmpAndIpmiSettingsAreMigrated(): void
    {
        LibrenmsConfig::set('icmp_check', true);

        $this->migrateLegacyDevices([
            ['device_id' => 1, 'hostname' => 'a'],
            ['device_id' => 2, 'hostname' => 'b'],
        ], [
            ['device_id' => 2, 'attrib_type' => 'override_icmp_disable', 'attrib_value' => 'true'],
            ['device_id' => 2, 'attrib_type' => 'ipmi_hostname', 'attrib_value' => 'bmc.example.com'],
            ['device_id' => 2, 'attrib_type' => 'ipmi_port', 'attrib_value' => '6230'],
            ['device_id' => 2, 'attrib_type' => 'ipmi_ciphersuite', 'attrib_value' => '3'],
            ['device_id' => 2, 'attrib_type' => 'ipmi_type', 'attrib_value' => 'lanplus'],
            ['device_id' => 2, 'attrib_type' => 'ipmi_username', 'attrib_value' => 'admin'],
            ['device_id' => 2, 'attrib_type' => 'ipmi_password', 'attrib_value' => 'secret'],
        ]);

        $this->assertEquals(1, $this->pollingMethod(1, 'icmp')->enabled);
        $this->assertEquals(0, $this->pollingMethod(2, 'icmp')->enabled);
        $this->assertSame(['ip_version' => 'match_snmp_transport'], json_decode($this->pollingMethod(1, 'icmp')->settings, true));

        $this->assertNull($this->pollingMethod(1, 'ipmi'));
        $ipmi = $this->pollingMethod(2, 'ipmi');
        $this->assertSame(['hostname' => 'bmc.example.com', 'port' => 6230, 'ciphersuite' => 3, 'type' => 'lanplus'], json_decode($ipmi->settings, true));
        $this->assertSame(['username' => 'admin', 'password' => 'secret', 'kg_key' => null], $this->secretData($ipmi->secret_id));
    }

    public function testUnixAgentIsOnlyAddedWhereTheModuleIsEnabled(): void
    {
        LibrenmsConfig::set('poller_modules.unix-agent', false);
        LibrenmsConfig::set('os.windows.poller_modules.unix-agent', true);

        $this->migrateLegacyDevices([
            ['device_id' => 1, 'hostname' => 'linux-default', 'os' => 'linux'],
            ['device_id' => 2, 'hostname' => 'linux-enabled', 'os' => 'linux'],
            ['device_id' => 3, 'hostname' => 'windows-os-enabled', 'os' => 'windows'],
            ['device_id' => 4, 'hostname' => 'windows-disabled', 'os' => 'windows'],
            ['device_id' => 5, 'hostname' => 'not-unix', 'os' => 'ios'],
        ], [
            ['device_id' => 2, 'attrib_type' => 'poll_unix-agent', 'attrib_value' => '1'],
            ['device_id' => 4, 'attrib_type' => 'poll_unix-agent', 'attrib_value' => '0'],
            ['device_id' => 5, 'attrib_type' => 'poll_unix-agent', 'attrib_value' => '1'],
        ]);

        $this->assertSame([2, 3], DB::table('device_polling_methods')->where('method_type', 'unix-agent')->orderBy('device_id')->pluck('device_id')->all());
    }

    public function testUnixAgentPortOverrideIsMigrated(): void
    {
        LibrenmsConfig::set('poller_modules.unix-agent', true);

        $this->migrateLegacyDevices([
            ['device_id' => 1, 'hostname' => 'custom-port', 'os' => 'linux'],
            ['device_id' => 2, 'hostname' => 'default-port', 'os' => 'linux'],
            ['device_id' => 3, 'hostname' => 'empty-port', 'os' => 'linux'],
        ], [
            ['device_id' => 1, 'attrib_type' => 'override_Unixagent_port', 'attrib_value' => '6557'],
            ['device_id' => 3, 'attrib_type' => 'override_Unixagent_port', 'attrib_value' => ''],
        ]);

        $this->assertSame(['port' => 6557], json_decode($this->pollingMethod(1, 'unix-agent')->settings, true));
        $this->assertNull($this->pollingMethod(2, 'unix-agent')->settings);
        $this->assertNull($this->pollingMethod(3, 'unix-agent')->settings); // legacy ignored an empty override
    }

    /**
     * Roll back to before the polling method migrations, add devices with legacy fields, then migrate.
     *
     * @param  array<int, array<string, mixed>>  $devices
     * @param  array<int, array<string, mixed>>  $attribs
     */
    private function migrateLegacyDevices(array $devices, array $attribs = []): void
    {
        $steps = DB::table('migrations')->where('migration', '>=', '2026_04_01_162956_migrate_snmp_secrets')->count();
        $this->artisan('migrate:rollback', ['--database' => $this->connection, '--step' => $steps]);

        foreach ($devices as $device) {
            DB::table('devices')->insert($device + ['os' => 'linux', 'status' => 1, 'status_reason' => '', 'snmp_disable' => 0]);
        }
        DB::table('devices_attribs')->insert($attribs);

        $this->artisan('migrate', ['--database' => $this->connection]);
    }

    private function pollingMethod(int $deviceId, string $type): ?object
    {
        return DB::table('device_polling_methods')->where(['device_id' => $deviceId, 'method_type' => $type])->first();
    }

    private function secret(int $id): object
    {
        return DB::table('secrets')->find($id);
    }

    /**
     * @return array<string, mixed>
     */
    private function secretData(int $id): array
    {
        return json_decode(decrypt($this->secret($id)->data), true);
    }
}
