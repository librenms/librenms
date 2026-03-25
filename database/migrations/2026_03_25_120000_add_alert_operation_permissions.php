<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    private array $permissions = [
        'alert-operation.view',
        'alert-operation.viewAny',
        'alert-operation.create',
        'alert-operation.update',
        'alert-operation.delete',
    ];

    public function up(): void
    {
        $now = Carbon::now();

        $insertData = array_map(static fn ($name) => [
            'name' => $name,
            'guard_name' => 'web',
            'created_at' => $now,
            'updated_at' => $now,
        ], $this->permissions);

        DB::table('permissions')->insertOrIgnore($insertData);
    }

    public function down(): void
    {
        DB::table('permissions')
            ->whereIn('name', $this->permissions)
            ->where('guard_name', 'web')
            ->delete();
    }
};
