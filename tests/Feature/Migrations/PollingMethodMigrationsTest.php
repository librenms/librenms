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
            ['device_id' => 2, 'hostname' => 'b', 'snmpver' => 'v2c', 'community' => 'shared', 'port' => 1161, 'transport' => 'tcp', 'snmp_disable' => 1],
            ['device_id' => 3, 'hostname' => 'c', 'snmpver' => 'v3', 'authlevel' => 'authPriv', 'authname' => 'user', 'authpass' => 'authpass', 'authalgo' => 'SHA', 'cryptopass' => 'cryptpass', 'cryptoalgo' => 'AES'],
        ], [
            ['device_id' => 1, 'attrib_type' => 'snmp_max_oid', 'attrib_value' => '20'],
            ['device_id' => 1, 'attrib_type' => 'snmp_bulk', 'attrib_value' => 'false'],
        ]);

        $a = $this->pollingMethod(1, 'snmp');
        $b = $this->pollingMethod(2, 'snmp');
        $c = $this->pollingMethod(3, 'snmp');

        // only values that differ from the defaults are kept
        $this->assertSame(['max_oid' => 20, 'bulk' => false], json_decode($a->settings, true));
        $this->assertSame(['port' => 1161, 'transport' => 'tcp'], json_decode($b->settings, true));
        $this->assertEquals(1, $a->enabled);
        $this->assertEquals(0, $b->enabled);

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
        $this->assertSame(['retries' => 0], json_decode($this->pollingMethod(1, 'snmp')->settings, true));
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
            ['device_id' => 2, 'attrib_type' => 'ipmi_username', 'attrib_value' => 'admin'],
            ['device_id' => 2, 'attrib_type' => 'ipmi_password', 'attrib_value' => 'secret'],
        ]);

        $this->assertEquals(1, $this->pollingMethod(1, 'icmp')->enabled);
        $this->assertEquals(0, $this->pollingMethod(2, 'icmp')->enabled);
        $this->assertSame(['ip_version' => 'match_snmp_transport'], json_decode($this->pollingMethod(1, 'icmp')->settings, true));

        $this->assertNull($this->pollingMethod(1, 'ipmi'));
        $ipmi = $this->pollingMethod(2, 'ipmi');
        $this->assertSame(['hostname' => 'bmc.example.com', 'port' => 6230, 'ciphersuite' => 3], json_decode($ipmi->settings, true));
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
