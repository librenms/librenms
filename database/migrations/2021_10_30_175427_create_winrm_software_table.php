<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWinRMSoftwareTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('winrm_software', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 250);
            $table->string('vendor', 1000)->nullable();
            $table->text('description')->nullable();
            $table->unique(['name','vendor']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('winrm_software');
    }
}
