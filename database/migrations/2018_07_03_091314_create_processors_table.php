<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProcessorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('processors', function (Blueprint $table) {
            $table->increments('processor_id');
            $table->integer('entPhysicalIndex')->default(0);
            $table->integer('hrDeviceIndex')->nullable();
            $table->unsignedInteger('device_id')->index();
            $table->string('processor_oid', 128);
            $table->string('processor_index', 32);
            $table->string('processor_type', 16);
            $table->integer('processor_usage');
            $table->string('processor_descr', 64);
            $table->integer('processor_precision')->default(1);
            $table->integer('processor_perc_warn')->nullable()->default(75);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('processors');
    }
}
