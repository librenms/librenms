<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateComponentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('component', function (Blueprint $table) {
            $table->increments('id')->comment('ID for each component, unique index');
            $table->unsignedInteger('device_id')->index()->comment('device_id from the devices table');
            $table->string('type', 50)->index()->comment('name from the component_type table');
            $table->string('label')->nullable()->comment('Display label for the component');
            $table->boolean('status')->default(0)->comment('The status of the component, retreived from the device');
            $table->boolean('disabled')->default(0)->comment('Should this component be polled');
            $table->boolean('ignore')->default(0)->comment('Should this component be alerted on');
            $table->string('error')->nullable()->comment('Error message if in Alert state');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('component');
    }
}
