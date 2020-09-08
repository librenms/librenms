<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDeviceRelationshipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_relationships', function (Blueprint $table) {
            $table->unsignedInteger('parent_device_id')->default(0);
            $table->unsignedInteger('child_device_id')->index();
            $table->primary(['parent_device_id', 'child_device_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('device_relationships');
    }
}
