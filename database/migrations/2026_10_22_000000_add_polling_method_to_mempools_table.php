<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mempools', function (Blueprint $table): void {
            $table->string('polling_method', 32)->default('snmp')->after('device_id');
        });
    }

    public function down(): void
    {
        Schema::table('mempools', function (Blueprint $table): void {
            $table->dropColumn('polling_method');
        });
    }
};
