<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * SNMP secrets held the version, now the version follows from the type of secret.
 * Split them into community and v3 secrets. A community is used as v2c, unless its method sets v1.
 * A v1 and a v2c secret with the same community become one secret.
 */
return new class extends Migration
{
    private const V3_KEYS = ['authname', 'authlevel', 'authpass', 'authalgo', 'cryptoalgo', 'cryptopass'];

    public function up(): void
    {
        /** @var array<string, int> $kept secret id by type and data */
        $kept = [];
        /** @var array<int, int|null> $replaced the secret that replaces a removed one, null if none */
        $replaced = [];

        foreach (DB::table('secrets')->where('secret_type', 'snmp')->orderBy('id')->get(['id', 'data']) as $secret) {
            // Failing to decrypt fails the migration, the type of the secret is not known without its data
            $data = json_decode((string) decrypt($secret->data), true, 512, JSON_THROW_ON_ERROR);
            $data = is_array($data) ? $data : [];
            $version = $data['version'] ?? 'v2c';

            if ($version === 'v3') {
                $type = 'snmp-v3';
                $newData = array_filter(array_intersect_key($data, array_flip(self::V3_KEYS)), fn ($v) => $v !== null);
                $newData['authname'] = ($newData['authname'] ?? '') ?: 'root'; // the user net-snmp was given without one
            } else {
                $type = 'snmp-community';
                $newData = ['community' => (string) ($data['community'] ?? '')];
            }
            ksort($newData);
            $key = $type . ':' . json_encode($newData);

            DB::transaction(function () use ($secret, $version, $type, $newData, $key, &$kept, &$replaced): void {
                if ($version === 'v1') {
                    $this->setVersion($secret->id, 'v1');
                }

                // a community secret with only a version had no credentials, the default credentials were used
                if ($type === 'snmp-community' && $newData['community'] === '') {
                    $this->replaceSecret($secret->id, null);
                    $replaced[$secret->id] = null;

                    return;
                }

                if (isset($kept[$key])) {
                    $this->replaceSecret($secret->id, $kept[$key]);
                    $replaced[$secret->id] = $kept[$key];

                    return;
                }

                DB::table('secrets')->where('id', $secret->id)->update([
                    'secret_type' => $type,
                    'data' => encrypt(json_encode($newData, JSON_THROW_ON_ERROR)),
                    'updated_at' => now(),
                ]);
                $kept[$key] = $secret->id;
            });
        }

        $this->updateDefaultCredentials($replaced);
    }

    /**
     * The secrets hold the version again. A community used with v1 and v2c becomes a secret for each version.
     */
    public function down(): void
    {
        foreach (DB::table('secrets')->whereIn('secret_type', ['snmp-community', 'snmp-v3'])->orderBy('id')->get() as $secret) {
            $data = json_decode((string) decrypt($secret->data), true, 512, JSON_THROW_ON_ERROR);
            $data = is_array($data) ? $data : [];

            $methodsByVersion = DB::table('device_polling_methods')
                ->where('method_type', 'snmp')
                ->where('secret_id', $secret->id)
                ->get(['id', 'settings'])
                ->groupBy(fn ($method) => $secret->secret_type === 'snmp-v3' ? 'v3' : ((json_decode((string) $method->settings, true)['version'] ?? null) === 'v1' ? 'v1' : 'v2c'));
            $versions = $methodsByVersion->keys()->all() ?: [$secret->secret_type === 'snmp-v3' ? 'v3' : 'v2c'];

            DB::transaction(function () use ($secret, $data, $methodsByVersion, $versions): void {
                $first = array_shift($versions);
                DB::table('secrets')->where('id', $secret->id)->update([
                    'secret_type' => 'snmp',
                    'data' => encrypt(json_encode(['version' => $first] + $data, JSON_THROW_ON_ERROR)),
                    'updated_at' => now(),
                ]);

                foreach ($versions as $version) {
                    $copyId = DB::table('secrets')->insertGetId([
                        'description' => $this->uniqueDescription("$secret->description ($version)"),
                        'secret_type' => 'snmp',
                        'data' => encrypt(json_encode(['version' => $version] + $data, JSON_THROW_ON_ERROR)),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    DB::table('device_polling_methods')->whereIn('id', $methodsByVersion[$version]->pluck('id'))->update(['secret_id' => $copyId]);
                }
            });
        }

        // the old settings have no version, it was in the secret
        DB::table('device_polling_methods')->where('method_type', 'snmp')->get(['id', 'settings'])->each(function ($method): void {
            $settings = json_decode((string) $method->settings, true);
            if (is_array($settings) && array_key_exists('version', $settings)) {
                unset($settings['version']);
                DB::table('device_polling_methods')->where('id', $method->id)->update(['settings' => json_encode($settings)]);
            }
        });
    }

    private function setVersion(int $secretId, string $version): void
    {
        $methods = DB::table('device_polling_methods')
            ->where('method_type', 'snmp')
            ->where('secret_id', $secretId)
            ->get(['id', 'settings']);

        foreach ($methods as $method) {
            $settings = json_decode((string) $method->settings, true);
            $settings = is_array($settings) ? $settings : [];
            $settings['version'] = $version;
            DB::table('device_polling_methods')->where('id', $method->id)->update(['settings' => json_encode($settings)]);
        }
    }

    private function replaceSecret(int $secretId, ?int $replacementId): void
    {
        DB::table('device_polling_methods')->where('secret_id', $secretId)->update(['secret_id' => $replacementId]);
        DB::table('secrets')->where('id', $secretId)->delete();
    }

    private function uniqueDescription(string $description): string
    {
        $unique = $description;
        for ($i = 2; DB::table('secrets')->where('description', $unique)->exists(); $i++) {
            $unique = "$description #$i";
        }

        return $unique;
    }

    /**
     * @param  array<int, int|null>  $replaced
     */
    private function updateDefaultCredentials(array $replaced): void
    {
        if (empty($replaced)) {
            return;
        }

        $value = DB::table('config')->where('config_name', 'snmp.default_credentials')->value('config_value');
        $ids = json_decode((string) $value, true);
        if (! is_array($ids)) {
            return;
        }

        $newIds = collect($ids)
            ->map(fn ($id) => array_key_exists((int) $id, $replaced) ? $replaced[(int) $id] : (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($newIds !== $ids) {
            DB::table('config')->where('config_name', 'snmp.default_credentials')->update(['config_value' => json_encode($newIds)]);
            \App\Facades\LibrenmsConfig::invalidateCache();
        }
    }
};
