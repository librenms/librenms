<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVlanAndPortIndexFieldsToPortsStpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ports_stp', function (Blueprint $table) {
            $table->unsignedInteger('vlan')->nullable()->after('device_id');
            $table->unsignedInteger('port_index')->after('port_id');
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
            $table->dropColumn(['vlan', 'port_index']);
        });
    }
}
