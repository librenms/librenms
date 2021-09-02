<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVrfsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vrfs', function (Blueprint $table) {
            $table->increments('vrf_id');
            $table->string('vrf_oid', 256);
            $table->string('vrf_name', 128)->nullable();
            $table->string('mplsVpnVrfRouteDistinguisher', 128)->nullable();
            $table->text('mplsVpnVrfDescription');
            $table->unsignedInteger('device_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('vrfs');
    }
}
