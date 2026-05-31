<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('mdadm_drives', 'dev_uuid')) {
            Schema::table('mdadm_drives', function (Blueprint $table) {
                // mdadm superblock Device UUID (stable across kernel-name changes); used as the per-drive rrd key.
                $table->string('dev_uuid', 64)->nullable()->after('dev_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('mdadm_drives', 'dev_uuid')) {
            Schema::table('mdadm_drives', function (Blueprint $table) {
                $table->dropColumn('dev_uuid');
            });
        }
    }
};
