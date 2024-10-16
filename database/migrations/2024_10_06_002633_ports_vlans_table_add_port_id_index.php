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
        Schema::table('ports_vlans', function (Blueprint $table) {
            $table->index('port_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ports_vlans', function (Blueprint $table) {
            $table->dropIndex('ports_vlans_port_id_index');
        });
    }
};
