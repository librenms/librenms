<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddColumnToPorts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ports', function (Blueprint $table) {
            $table->unsignedInteger('port_group_id')->default(null)->after('portName');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ports', function (Blueprint $table) {
            $table->dropColumn(['port_group_id']);
        });
    }
}
