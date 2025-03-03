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
            $table->dropIndex('ports_stp_device_id_port_id_unique');
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
            $table->unique(['device_id', 'port_id']);
        });
    }
};
