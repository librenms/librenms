<?php

use Illuminate\Contracts\Encryption\DecryptException;
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
        // secrets split by an earlier run that failed part way, the same credentials are still merged into them
        /** @var array<string, int> $kept secret id by type and data */
        $kept = [];
        foreach (DB::table('secrets')->whereIn('secret_type', ['snmp-community', 'snmp-v3'])->orderBy('id')->get(['id', 'secret_type', 'data']) as $secret) {
            $kept[$this->key($secret->secret_type, $this->decrypt($secret))] ??= $secret->id;
        }

        foreach (DB::table('secrets')->where('secret_type', 'snmp')->orderBy('id')->get(['id', 'data']) as $secret) {
            $data = $this->decrypt($secret);
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
            $key = $this->key($type, $newData);

            DB::transaction(function () use ($secret, $version, $type, $newData, $key, &$kept): void {
                if ($version === 'v1') {
                    $this->setVersion($secret->id, 'v1');
                }

                if (isset($kept[$key])) {
                    $this->replaceSecret($secret->id, $kept[$key]);

                    return;
                }

                DB::table('secrets')->where('id', $secret->id)->update([
                    'secret_type' => $type,
                    'data' => encrypt(json_encode($newData, JSON_THROW_ON_ERROR)),
                    'updated_at' => now(),
                ]);
                $kept[$key] = $secret->id;
            }, 3); // pollers keep running, retry if they changed a method at the same time
        }
    }

    /**
     * The secrets hold the version again. A community used with v1 and v2c becomes a secret for each version.
     */
    public function down(): void
    {
        foreach (DB::table('secrets')->whereIn('secret_type', ['snmp-community', 'snmp-v3'])->orderBy('id')->get() as $secret) {
            $data = $this->decrypt($secret);
            $isV3 = $secret->secret_type === 'snmp-v3';

            // a community is v2c unless its method sets v1
            $methodsByVersion = DB::table('device_polling_methods')
                ->where('method_type', 'snmp')
                ->where('secret_id', $secret->id)
                ->get(['id', 'settings'])
                ->groupBy(fn ($method) => $isV3 ? 'v3' : ((json_decode((string) $method->settings, true)['version'] ?? null) === 'v1' ? 'v1' : 'v2c'));
            $versions = $methodsByVersion->keys()->all() ?: [$isV3 ? 'v3' : 'v2c'];

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

    /**
     * Failing to decrypt fails the migration, the type of the secret is not known without its data.
     * Each secret is split in its own transaction, so it can run again once APP_KEY is fixed.
     *
     * @return array<string, mixed>
     */
    private function decrypt(object $secret): array
    {
        try {
            $data = json_decode((string) decrypt($secret->data), true, 512, JSON_THROW_ON_ERROR);
        } catch (DecryptException|JsonException $e) {
            throw new RuntimeException("SNMP secret $secret->id could not be decrypted. Every poller must use the APP_KEY the secrets were encrypted with.", previous: $e);
        }

        return is_array($data) ? $data : [];
    }

    private function setVersion(int $secretId, string $version): void
    {
        $methods = DB::table('device_polling_methods')
            ->where('method_type', 'snmp')
            ->where('secret_id', $secretId)
            ->lockForUpdate() // a plain read takes a snapshot, MariaDB fails the update if a poller changed a method since
            ->get(['id', 'settings']);

        foreach ($methods as $method) {
            $settings = json_decode((string) $method->settings, true);
            $settings = is_array($settings) ? $settings : [];
            $settings['version'] = $version;
            DB::table('device_polling_methods')->where('id', $method->id)->update(['settings' => json_encode($settings)]);
        }
    }

    /**
     * Credentials with the same type and data are the same secret.
     *
     * @param  array<string, mixed>  $data
     */
    private function key(string $type, array $data): string
    {
        ksort($data);

        return $type . ':' . json_encode($data);
    }

    /**
     * Replace the secret everywhere it is used, in the same transaction, so a later run does not find it.
     */
    private function replaceSecret(int $secretId, int $replacementId): void
    {
        DB::table('device_polling_methods')->where('secret_id', $secretId)->update(['secret_id' => $replacementId]);
        DB::table('secrets')->where('id', $secretId)->delete();
        $this->replaceDefaultCredential($secretId, $replacementId);
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
     * The default credentials keep their order, with the replacement where the secret was.
     */
    private function replaceDefaultCredential(int $secretId, int $replacementId): void
    {
        $value = DB::table('config')->where('config_name', 'snmp.default_credentials')->lockForUpdate()->value('config_value');
        $ids = json_decode((string) $value, true);
        if (! is_array($ids)) {
            return;
        }

        $newIds = collect($ids)
            ->map(fn ($id) => (int) $id === $secretId ? $replacementId : (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($newIds !== $ids) {
            DB::table('config')->where('config_name', 'snmp.default_credentials')->update(['config_value' => json_encode($newIds)]);
            \App\Facades\LibrenmsConfig::invalidateCache();
        }
    }
};
