<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePortsStackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ports_stack', function (Blueprint $table) {
            $table->unsignedInteger('device_id');
            $table->unsignedInteger('port_id_high');
            $table->unsignedInteger('port_id_low');
            $table->string('ifStackStatus', 32);
            $table->unique(['device_id', 'port_id_high', 'port_id_low']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ports_stack');
    }
}
