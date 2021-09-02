<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVrfLiteCiscoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vrf_lite_cisco', function (Blueprint $table) {
            $table->increments('vrf_lite_cisco_id');
            $table->unsignedInteger('device_id')->index();
            $table->string('context_name', 128)->index();
            $table->string('intance_name', 128)->nullable()->default('');
            $table->string('vrf_name', 128)->nullable()->default('Default')->index();
            $table->index(['device_id', 'context_name', 'vrf_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('vrf_lite_cisco');
    }
}
