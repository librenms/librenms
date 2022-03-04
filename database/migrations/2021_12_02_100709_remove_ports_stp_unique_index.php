<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePortsStpUniqueIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
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
    public function down()
    {
        Schema::table('ports_stp', function (Blueprint $table) {
            $table->unique(['device_id', 'port_index']);
        });
    }
}
