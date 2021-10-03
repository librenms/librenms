<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateHrSystemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hrSystem', function (Blueprint $table) {
            $table->integer('hrSystemNumUsers')->default(0)->nullable()->change();
            $table->integer('hrSystemProcesses')->default(0)->nullable()->change();
            $table->integer('hrSystemMaxProcesses')->default(0)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hrSystem', function (Blueprint $table) {
            $table->integer('hrSystemNumUsers')->default(0)->nullable(false)->change();
            $table->integer('hrSystemProcesses')->default(0)->nullable(false)->change();
            $table->integer('hrSystemMaxProcesses')->default(0)->nullable(false)->change();
        });
    }
}
