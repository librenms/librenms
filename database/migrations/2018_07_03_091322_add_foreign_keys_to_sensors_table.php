<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToSensorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sensors', function (Blueprint $table) {
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
            Schema::table('sensors', function (Blueprint $table) {
                $table->dropForeign('sensors_device_id_foreign');
            });
        }
    }
}
