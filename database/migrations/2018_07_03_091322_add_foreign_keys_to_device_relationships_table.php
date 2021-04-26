<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToDeviceRelationshipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('device_relationships', function (Blueprint $table) {
            $table->foreign('child_device_id', 'device_relationship_child_device_id_fk')->references('device_id')->on('devices')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('parent_device_id', 'device_relationship_parent_device_id_fk')->references('device_id')->on('devices')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (\LibreNMS\DB\Eloquent::getDriver() !== 'sqlite') {
            Schema::table('device_relationships', function (Blueprint $table) {
                $table->dropForeign('device_relationship_child_device_id_fk');
                $table->dropForeign('device_relationship_parent_device_id_fk');
            });
        }
    }
}
