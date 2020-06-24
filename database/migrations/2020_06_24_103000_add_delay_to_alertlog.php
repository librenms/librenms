<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDelayToAlertlog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('alert_log', function (Blueprint $table) {
            $table->boolean('delay')->default(0);
        });
        // retro-compatibility - consider that all previous alert logs lasted longer than alert rule delay
        \DB::statement("UPDATE alert_log SET delay=1;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('alert_log', function (Blueprint $table) {
            $table->dropColumn('delay');
        });
    }
}
