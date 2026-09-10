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
        Schema::table('device_outages', function (Blueprint $table) {
            $table->index('going_down');
            $table->index('up_again');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_outages', function (Blueprint $table) {
            $table->dropIndex('going_down');
            $table->dropIndex('up_again');
        });
    }
};
