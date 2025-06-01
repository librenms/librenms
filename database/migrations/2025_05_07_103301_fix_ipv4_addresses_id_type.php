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
        // Clean invalid values in ipv4_network_id
        DB::table('ipv4_addresses')
            ->whereNull('ipv4_network_id')
            ->orWhere('ipv4_network_id', '')
            ->update(['ipv4_network_id' => 0]);

        if (LibreNMS\DB\Eloquent::getDriver() !== 'sqlite') {
            Schema::table('ipv4_addresses', function (Blueprint $table) {
                $table->unsignedInteger('ipv4_network_id')->default(0)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ipv4_addresses', function (Blueprint $table) {
            $table->string('ipv4_network_id', 128)->change();
        });
    }
};
