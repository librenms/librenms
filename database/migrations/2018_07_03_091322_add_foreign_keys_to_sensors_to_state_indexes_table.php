<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToSensorsToStateIndexesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sensors_to_state_indexes', function (Blueprint $table) {
            $table->foreign('state_index_id', 'sensors_to_state_indexes_ibfk_1')->references('state_index_id')->on('state_indexes')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('sensor_id')->references('sensor_id')->on('sensors')->onUpdate('RESTRICT')->onDelete('CASCADE');
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
            Schema::table('sensors_to_state_indexes', function (Blueprint $table) {
                $table->dropForeign('sensors_to_state_indexes_ibfk_1');
                $table->dropForeign('sensors_to_state_indexes_sensor_id_foreign');
            });
        }
    }
}
