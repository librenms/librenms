<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table): void {
            $table->string('polling_method', 32)->default('snmp')->after('device_id');
        });

        // package sections from the unix agent predate polling_method, mark those rows as agent-derived
        DB::table('packages')->where('manager', '!=', 'host-res')->update(['polling_method' => 'unix-agent']);

        // the status flag only tracked the Package::sync replacement, the modules sync per method now
        Schema::table('packages', function (Blueprint $table): void {
            $table->dropColumn('status');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table): void {
            $table->boolean('status')->default(true)->after('manager');
        });

        Schema::table('packages', function (Blueprint $table): void {
            $table->dropColumn('polling_method');
        });
    }
};
