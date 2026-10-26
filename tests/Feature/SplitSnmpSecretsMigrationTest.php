<?php

namespace LibreNMS\Tests\Feature;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Tests\DBTestCase;

final class SplitSnmpSecretsMigrationTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testSnmpSecretsAreSplitByType(): void
    {
        $v2c = $this->oldSecret('split v2c', ['version' => 'v2c', 'community' => 'split-test']);
        $v1 = $this->oldSecret('split v1', ['version' => 'v1', 'community' => 'split-test']);
        $v3 = $this->oldSecret('split v3', ['version' => 'v3', 'authlevel' => 'authNoPriv', 'authname' => 'user', 'authpass' => 'password1', 'authalgo' => 'SHA']);
        $v3NoName = $this->oldSecret('split v3 no name', ['version' => 'v3', 'authlevel' => 'authPriv', 'authname' => '', 'authpass' => 'password2', 'cryptopass' => 'crypt2']);
        $versionOnly = $this->oldSecret('split version only', ['version' => 'v1']);
        $ipmi = Secret::create(['description' => 'split ipmi', 'secret_type' => SecretType::Ipmi, 'data' => ['username' => 'admin']]);

        $v2cMethod = $this->snmpMethod($v2c);
        $v1Method = $this->snmpMethod($v1, ['port' => 1161]);
        $v3Method = $this->snmpMethod($v3);
        $v3NoNameMethod = $this->snmpMethod($v3NoName);
        $versionOnlyMethod = $this->snmpMethod($versionOnly);

        $otherDefault = Secret::create(['description' => 'split other default', 'secret_type' => SecretType::SnmpCommunity, 'data' => ['community' => 'other']]);
        DB::table('config')->updateOrInsert(['config_name' => 'snmp.default_credentials'], ['config_value' => json_encode([$v1, $otherDefault->id, $v2c, $versionOnly, $v3])]);

        $this->migration()->up();

        // the v1 and v2c secrets have the same community, the first one is kept
        $community = Secret::findOrFail($v2c);
        $this->assertSame(SecretType::SnmpCommunity, $community->secret_type);
        $this->assertSame(['community' => 'split-test'], $community->data);
        $this->assertNull(Secret::find($v1));

        $v3Secret = Secret::findOrFail($v3);
        $this->assertSame(SecretType::SnmpV3, $v3Secret->secret_type);
        $this->assertEquals(['authlevel' => 'authNoPriv', 'authname' => 'user', 'authpass' => 'password1', 'authalgo' => 'SHA'], $v3Secret->data);

        // net-snmp used root without a name, the passwords are kept
        $this->assertEquals(['authlevel' => 'authPriv', 'authname' => 'root', 'authpass' => 'password2', 'cryptopass' => 'crypt2'], Secret::findOrFail($v3NoName)->data);

        // net-snmp was given an empty community, the method keeps its secret
        $empty = Secret::findOrFail($versionOnly);
        $this->assertSame(SecretType::SnmpCommunity, $empty->secret_type);
        $this->assertSame(['community' => ''], $empty->data);

        $this->assertSame(SecretType::Ipmi, $ipmi->fresh()->secret_type);

        // only v1 is a setting, the type of secret gives the rest
        $this->assertMethod($v2cMethod, $v2c, []);
        $this->assertMethod($v1Method, $v2c, ['port' => 1161, 'version' => 'v1']);
        $this->assertMethod($v3Method, $v3, []);
        $this->assertMethod($v3NoNameMethod, $v3NoName, []);
        $this->assertMethod($versionOnlyMethod, $versionOnly, ['version' => 'v1']);

        // the order is kept, with the merged secret replaced
        $this->assertSame([$v2c, $otherDefault->id, $versionOnly, $v3], json_decode(DB::table('config')->where('config_name', 'snmp.default_credentials')->value('config_value'), true));

        // nothing left to do
        $this->migration()->up();
        $this->assertSame(SecretType::SnmpCommunity, $community->fresh()->secret_type);
    }

    public function testARunAfterAFailedRunStillMergesAndReplacesDefaults(): void
    {
        // split by the run that failed
        $split = Secret::create(['description' => 'rerun split', 'secret_type' => SecretType::SnmpCommunity, 'data' => ['community' => 'rerun']]);
        $v1 = $this->oldSecret('rerun v1', ['version' => 'v1', 'community' => 'rerun']);
        $v1Method = $this->snmpMethod($v1);
        DB::table('config')->updateOrInsert(['config_name' => 'snmp.default_credentials'], ['config_value' => json_encode([$v1, $split->id])]);

        $this->migration()->up();

        $this->assertNull(Secret::find($v1));
        $this->assertMethod($v1Method, $split->id, ['version' => 'v1']);
        $this->assertSame([$split->id], json_decode(DB::table('config')->where('config_name', 'snmp.default_credentials')->value('config_value'), true));
    }

    public function testDownGivesEachVersionOfACommunityItsOwnSecret(): void
    {
        $v2c = $this->oldSecret('down v2c', ['version' => 'v2c', 'community' => 'split-test']);
        $v1 = $this->oldSecret('down v1', ['version' => 'v1', 'community' => 'split-test']);
        $v3 = $this->oldSecret('down v3', ['version' => 'v3', 'authlevel' => 'noAuthNoPriv', 'authname' => 'user']);
        $v2cMethod = $this->snmpMethod($v2c);
        $v1Method = $this->snmpMethod($v1, ['port' => 1161]);
        $v3Method = $this->snmpMethod($v3);

        $this->migration()->up();
        $this->migration()->down();

        $this->assertSame(['snmp', ['version' => 'v2c', 'community' => 'split-test']], $this->rawSecret(DevicePollingMethod::findOrFail($v2cMethod)->secret_id));
        $this->assertSame(['snmp', ['version' => 'v3', 'authlevel' => 'noAuthNoPriv', 'authname' => 'user']], $this->rawSecret(DevicePollingMethod::findOrFail($v3Method)->secret_id));

        $v1Snmp = DevicePollingMethod::findOrFail($v1Method);
        $this->assertNotSame($v2c, $v1Snmp->secret_id);
        $this->assertSame(['snmp', ['version' => 'v1', 'community' => 'split-test']], $this->rawSecret($v1Snmp->secret_id));
        $this->assertSame('down v2c (v1)', DB::table('secrets')->where('id', $v1Snmp->secret_id)->value('description'));
        $this->assertSame(['port' => 1161], $v1Snmp->settings);
    }

    /**
     * @return array{string, array<string, mixed>}
     */
    private function rawSecret(?int $id): array
    {
        $secret = DB::table('secrets')->where('id', $id)->first();
        $this->assertNotNull($secret);

        return [$secret->secret_type, json_decode((string) decrypt($secret->data), true)];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function oldSecret(string $description, array $data): int
    {
        return DB::table('secrets')->insertGetId([
            'description' => $description,
            'secret_type' => 'snmp',
            'data' => encrypt(json_encode($data)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    private function snmpMethod(?int $secretId, array $settings = []): int
    {
        return DB::table('device_polling_methods')->insertGetId([
            'device_id' => Device::factory()->create()->device_id,
            'method_type' => PollingMethodType::Snmp->value,
            'secret_id' => $secretId,
            'settings' => json_encode($settings),
        ]);
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    private function assertMethod(int $id, ?int $secretId, array $settings): void
    {
        $method = DevicePollingMethod::findOrFail($id);
        $this->assertSame($secretId, $method->secret_id);
        $this->assertEquals($settings, $method->settings);
    }

    private function migration(): object
    {
        return require base_path('database/migrations/2026_10_20_000000_split_snmp_secrets.php');
    }
}
