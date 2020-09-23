<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCefSwitchingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cef_switching', function (Blueprint $table) {
            $table->increments('cef_switching_id');
            $table->unsignedInteger('device_id');
            $table->integer('entPhysicalIndex');
            $table->string('afi', 4);
            $table->integer('cef_index');
            $table->string('cef_path', 16);
            $table->integer('drop');
            $table->integer('punt');
            $table->integer('punt2host');
            $table->integer('drop_prev');
            $table->integer('punt_prev');
            $table->integer('punt2host_prev');
            $table->unsignedInteger('updated');
            $table->unsignedInteger('updated_prev');
            $table->unique(['device_id', 'entPhysicalIndex', 'afi', 'cef_index']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('cef_switching');
    }
}
