<?php

use App\Facades\Binaries;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Binary locations and versions are detected on each node, they no longer belong in the global config
 */
return new class extends Migration
{
    public function up(): void
    {
        $binaries = [
            'fping',
            'fping6',
            'ipmitool',
            'nfdump',
            'rrdtool',
            'snmpbulkwalk',
            'snmpget',
            'snmpgetnext',
            'snmptranslate',
            'snmptrap',
            'snmpwalk',
            'traceroute',
            'virsh',
        ];

        // each node imports these when it has no path cached, if they exist on that node
        $paths = DB::table('config')->whereIn('config_name', $binaries)->pluck('config_value', 'config_name')
            ->map(fn ($value) => json_decode((string) $value))
            ->filter(fn ($path) => is_string($path) && str_starts_with($path, '/'))
            ->all();

        if ($paths) {
            try {
                Cache::forever(Binaries::LEGACY_CACHE_KEY, $paths);
            } catch (Exception) {
                // no shared cache, every node will detect the paths
            }
        }

        DB::table('config')->whereIn('config_name', [...$binaries, 'snmp.unescape'])->delete();

        // rrdtool_version is still a setting to limit features, but it was saved automatically from the version on one node.
        // A lower version may have been set on purpose, a higher one is not supported here.
        $version = json_decode((string) DB::table('config')->where('config_name', 'rrdtool_version')->value('config_value'));
        $installed = Binaries::rrdtoolVersion();
        if (is_string($version) && $installed !== '' && version_compare($version, $installed, '>=')) {
            DB::table('config')->where('config_name', 'rrdtool_version')->delete();
        }
    }
};
