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
            $table->bigInteger('ifSpeed_prev')->nullable()->after('ifSpeed');
            $table->integer('ifHighSpeed_prev')->nullable()->after('ifHighSpeed');
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
            $table->dropColumn(['ifSpeed_prev', 'ifHighSpeed_prev']);
        });
    }
}
