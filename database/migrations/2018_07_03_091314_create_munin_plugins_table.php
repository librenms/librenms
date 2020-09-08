<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMuninPluginsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('munin_plugins', function (Blueprint $table) {
            $table->increments('mplug_id');
            $table->unsignedInteger('device_id')->index();
            $table->string('mplug_type');
            $table->string('mplug_instance', 128)->nullable();
            $table->string('mplug_category', 32)->nullable();
            $table->string('mplug_title', 128)->nullable();
            $table->text('mplug_info')->nullable();
            $table->string('mplug_vlabel', 128)->nullable();
            $table->string('mplug_args', 512)->nullable();
            $table->boolean('mplug_total')->default(0);
            $table->boolean('mplug_graph')->default(1);
            $table->unique(['device_id', 'mplug_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('munin_plugins');
    }
}
