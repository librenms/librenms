<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Replace the Oxidized-specific refresh permission with the generic device
     * config refresh permission, preserving which roles hold it.
     */
    public function up(): void
    {
        $this->movePermission('oxidized.refresh', 'device.refreshConfig');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->movePermission('device.refreshConfig', 'oxidized.refresh');
    }

    /**
     * Create $to (if missing), copy every role assignment from $from onto it,
     * then drop $from — moving the permission without losing which roles use it.
     */
    private function movePermission(string $from, string $to): void
    {
        DB::table('permissions')->insertOrIgnore([
            'name' => $to,
            'guard_name' => 'web',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $toId = DB::table('permissions')->where('name', $to)->where('guard_name', 'web')->value('id');
        $fromId = DB::table('permissions')->where('name', $from)->where('guard_name', 'web')->value('id');

        if (! $toId || ! $fromId) {
            return;
        }

        $rows = DB::table('role_has_permissions')
            ->where('permission_id', $fromId)
            ->pluck('role_id')
            ->map(fn ($roleId) => ['permission_id' => $toId, 'role_id' => $roleId])
            ->all();

        if ($rows !== []) {
            DB::table('role_has_permissions')->insertOrIgnore($rows);
        }

        // Removing the permission cascades its role assignments away.
        DB::table('permissions')->where('id', $fromId)->delete();
    }
};
