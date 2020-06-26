<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddAlertTransportTimerange extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('alert_transports', function (Blueprint $table) {
            $table->boolean('timerange')->default(0)->unsigned();
            $table->time('start_hr')->nullable();
            $table->time('end_hr')->nullable();
            $table->string('day', 15)->nullable();
            $table->boolean('invert_map')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('alert_transports', function (Blueprint $table) {
            $table->dropColumn(['timerange', 'start_hr', 'end_hr', 'day', 'invert_map']);
        });
    }
}
