<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDevicesAttribsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices_attribs', function (Blueprint $table) {
            $table->increments('attrib_id');
            $table->unsignedInteger('device_id')->index('device_id');
            $table->string('attrib_type', 32);
            $table->text('attrib_value', 65535);
            $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('devices_attribs');
    }
}
