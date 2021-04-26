<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddInvertMapToAlertRules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('alert_rules', function (Blueprint $table) {
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
        Schema::table('alert_rules', function (Blueprint $table) {
            $table->dropColumn(['invert_map']);
        });
    }
}
