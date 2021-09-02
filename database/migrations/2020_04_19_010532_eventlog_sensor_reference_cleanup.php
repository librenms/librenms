<?php

use Illuminate\Database\Migrations\Migration;

class EventlogSensorReferenceCleanup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (\App\Models\Sensor::getTypes() as $type) {
            DB::table('eventlog')->where('type', ucfirst($type))->update(['type' => $type]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach (\App\Models\Sensor::getTypes() as $type) {
            DB::table('eventlog')->where('type', $type)->update(['type' => ucfirst($type)]);
        }
    }
}
