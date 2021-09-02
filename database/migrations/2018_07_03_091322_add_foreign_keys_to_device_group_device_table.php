<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToDeviceGroupDeviceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('device_group_device', function (Blueprint $table) {
            $table->foreign('device_group_id')->references('id')->on('device_groups')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('device_id')->references('device_id')->on('devices')->onUpdate('RESTRICT')->onDelete('CASCADE');
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
            Schema::table('device_group_device', function (Blueprint $table) {
                $table->dropForeign('device_group_device_device_group_id_foreign');
                $table->dropForeign('device_group_device_device_id_foreign');
            });
        }
    }
}
