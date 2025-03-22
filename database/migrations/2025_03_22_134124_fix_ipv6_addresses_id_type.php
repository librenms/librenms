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
        // Clean invalid values in ipv6_network_id
        DB::table('ipv6_addresses')
            ->whereNull('ipv6_network_id')
            ->orWhere('ipv6_network_id', '')
            ->update(['ipv6_network_id' => 0]);

        Schema::table('ipv6_addresses', function (Blueprint $table) {
            $table->unsignedInteger('ipv6_network_id')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ipv6_addresses', function (Blueprint $table) {
            $table->string('ipv6_network_id', 128)->change();
        });
    }
};
