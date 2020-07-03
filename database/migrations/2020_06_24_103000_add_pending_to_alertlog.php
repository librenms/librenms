<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddpendingToAlertlog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('alert_log', function (Blueprint $table) {
            $table->boolean('pending')->default(1);
        });
        // retro-compatibility - consider that all previous alert logs lasted longer than alert rule pending
        \DB::statement("UPDATE alert_log SET pending=0;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('alert_log', function (Blueprint $table) {
            $table->dropColumn('pending');
        });
    }
}
