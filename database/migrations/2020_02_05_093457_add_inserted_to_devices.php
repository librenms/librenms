<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInsertedToDevices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('devices', function (Blueprint $table) {
            // add inserted column after device id with a default of current_timestamp
            $table->timestamp('inserted')->default(DB::raw('CURRENT_TIMESTAMP'))->after('device_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('devices', function (Blueprint $table) {
            // revert add inserted column after device id with a default of current_timestamp
            $table->dropColumn('inserted');
        });
    }
}
