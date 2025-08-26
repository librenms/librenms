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
        Schema::table('ipv4_addresses', function (Blueprint $table) {
            $table->index(['ipv4_network_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ipv4_addresses', function (Blueprint $table) {
            $table->dropIndex(['ipv4_network_id']);
        });
    }
};
