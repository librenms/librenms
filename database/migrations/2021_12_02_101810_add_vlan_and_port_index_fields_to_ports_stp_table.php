<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('ports_stp', function (Blueprint $table) {
            $table->unsignedInteger('vlan')->nullable()->after('device_id');
            $table->unsignedInteger('port_index')->after('port_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('ports_stp', function (Blueprint $table) {
            $table->dropColumn(['vlan', 'port_index']);
        });
    }
};
