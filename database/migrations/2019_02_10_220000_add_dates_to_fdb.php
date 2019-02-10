<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddDatesToFdb extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ports_fdb', function (Blueprint $table) {
            $table->timestamp('date_discovered')->nullable();
            $table->timestamp('date_last_seen')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ports_fdb', function (Blueprint $table) {
            $table->dropColumn('date_discovered');
            $table->dropColumn('date_last_seen');
        });
    }
}
