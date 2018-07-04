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
            $table->integer('parent_device_id')->unsigned()->default(0);
            $table->integer('child_device_id')->unsigned()->index('device_relationship_child_device_id_fk');
            $table->primary(['parent_device_id','child_device_id']);
        });

        \DB::statement("ALTER TABLE `device_relationships` CHANGE `parent_device_id` `parent_device_id` int(11) unsigned NOT NULL DEFAULT '0' ;");
        \DB::statement("ALTER TABLE `device_relationships` CHANGE `child_device_id` `child_device_id` int(11) unsigned NOT NULL ;");
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
