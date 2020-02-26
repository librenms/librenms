<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePortsLldplocportidTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ports_lldplocportid', function (Blueprint $table) {
            $table->unsignedInteger('device_id');
            $table->string('lldpLocPortId');
            $table->unsignedInteger('port_id')->nullable();
            $table->unique(['device_id', 'lldpLocPortId']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ports_lldplocportid');
    }
}
