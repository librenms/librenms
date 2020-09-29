<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAlertLogIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('alert_log', function (Blueprint $table) {
            $table->index(['rule_id', 'device_id']);
            $table->index(['rule_id', 'device_id', 'state']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('alert_log', function (Blueprint $table) {
            $table->dropIndex(['rule_id', 'device_id']);
            $table->dropIndex(['rule_id', 'device_id', 'state']);
        });
    }
}
