<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vlans', function (Blueprint $table) {
            $table->dropColumn('vlan_mtu');
            $table->boolean('vlan_state')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vlans', function (Blueprint $table) {
            $table->integer('vlan_mtu')->nullable();
            $table->dropColumn('vlan_state');
        });
    }
};
