<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PERMISSIONS = [
        'alert-template.viewAny',
        'alert-transport.viewAny',
        'poller-cluster.viewAny',
        'poller-cluster.view',
        'poller-cluster.update',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = Carbon::now();

        $insertData = array_map(fn ($name) => [
            'name' => $name,
            'guard_name' => 'web',
            'created_at' => $now,
            'updated_at' => $now,
        ], self::PERMISSIONS);

        DB::table('permissions')->insertOrIgnore($insertData);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')
            ->whereIn('name', self::PERMISSIONS)
            ->where('guard_name', 'web')
            ->delete();
    }
};
