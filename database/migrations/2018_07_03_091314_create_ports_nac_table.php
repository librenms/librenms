<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePortsNacTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ports_nac', function (Blueprint $table) {
            $table->increments('ports_nac_id');
            $table->string('auth_id', 50);
            $table->unsignedInteger('device_id')->index();
            $table->unsignedInteger('port_id');
            $table->string('domain', 50);
            $table->string('username', 50);
            $table->string('mac_address', 50);
            $table->string('ip_address', 50);
            $table->string('host_mode', 50);
            $table->string('authz_status', 50);
            $table->string('authz_by', 50);
            $table->string('authc_status', 50);
            $table->string('method', 50);
            $table->string('timeout', 50);
            $table->string('time_left', 50);
            $table->index(['port_id', 'mac_address']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ports_nac');
    }
}
