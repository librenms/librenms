<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = Carbon\Carbon::now();

        DB::table('roles')->insertOrIgnore([
            'name' => 'admin',
            'guard_name' => 'web',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('roles')->insertOrIgnore([
            'name' => 'global-read',
            'guard_name' => 'web',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('roles')->insertOrIgnore([
            'name' => 'user',
            'guard_name' => 'web',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
