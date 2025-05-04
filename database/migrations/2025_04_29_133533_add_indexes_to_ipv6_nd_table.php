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
        Schema::table('ipv6_nd', function (Blueprint $table) {
            $table->index(['port_id']);
            $table->index(['device_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ipv6_nd', function (Blueprint $table) {
            $table->dropIndex(['port_id']);
            $table->dropIndex(['device_id']);
        });
    }
};
