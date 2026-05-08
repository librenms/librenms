<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $permissions = [
        'ssl-certificate.create',
        'ssl-certificate.delete',
        'ssl-certificate.view',
        'ssl-certificate.viewAny',
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
        ], $this->permissions);

        DB::table('permissions')->insertOrIgnore($insertData);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->whereIn('name', $this->permissions)->where('guard_name', 'web')->delete();
    }
};
