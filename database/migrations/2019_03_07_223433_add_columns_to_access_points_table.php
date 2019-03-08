<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddColumnsToAccessPointsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('access_points', function (Blueprint $table) {
            $table->string('extchannel', 24);
            $table->string('htchannel', 6);
            $table->string('htmode', 16);
            $table->string('mode', 32);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('access_points', function (Blueprint $table) {
            $table->dropColumn('extchannel');
            $table->dropColumn('htchannel');
            $table->dropColumn('htmode');
            $table->dropColumn('mode');
        });
    }
}
