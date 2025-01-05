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
        Schema::table('devices', function (Blueprint $table) {
            $table->index(['hostname', 'sysName', 'display']);
            $table->dropIndex('devices_hostname_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->index('hostname');
            $table->dropIndex('devices_hostname_sysname_display_index');
        });
    }
};
