<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToComponentPrefsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('component_prefs', function (Blueprint $table) {
            $table->foreign('component', 'component_prefs_ibfk_1')->references('id')->on('component')->onUpdate('CASCADE')->onDelete('CASCADE');
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
            Schema::table('component_prefs', function (Blueprint $table) {
                $table->dropForeign('component_prefs_ibfk_1');
            });
        }
    }
}
