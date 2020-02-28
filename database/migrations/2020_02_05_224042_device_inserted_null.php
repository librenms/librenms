<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeviceInsertedNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('devices', function (Blueprint $table) {
            // inserted column will not default null to CURRENT_TIMESTAMP
            \DB::statement("alter table `devices` change `inserted` `inserted` timestamp NULL default CURRENT_TIMESTAMP;");
            \DB::statement("update `devices` set `inserted`=NULL;"); // set all existing (legacy) rows to null
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
            // inserted column will default null to CURRENT_TIMESTAMP
            \DB::statement("alter table `devices` change `inserted` `inserted` timestamp default CURRENT_TIMESTAMP;");
            \DB::statement("update `devices` set `inserted`=NULL"); // timestamp all existing (legacy) rows to now()
        });
    }
}
