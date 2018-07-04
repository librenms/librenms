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
            $table->integer('mplug_id', true);
            $table->integer('device_id')->index('device_id');
            $table->string('mplug_type');
            $table->string('mplug_instance', 128)->nullable();
            $table->string('mplug_category', 32)->nullable();
            $table->string('mplug_title', 128)->nullable();
            $table->text('mplug_info', 65535)->nullable();
            $table->string('mplug_vlabel', 128)->nullable();
            $table->string('mplug_args', 512)->nullable();
            $table->boolean('mplug_total', 1)->default(0);
            $table->boolean('mplug_graph', 1)->default(1);
            $table->unique(['device_id','mplug_type'], 'UNIQUE');
        });

        \DB::statement("ALTER TABLE `munin_plugins` CHANGE `mplug_total` `mplug_total` binary(1) NOT NULL DEFAULT '0' ;");
        \DB::statement("ALTER TABLE `munin_plugins` CHANGE `mplug_graph` `mplug_graph` binary(1) NOT NULL DEFAULT '1' ;");
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
