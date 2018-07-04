<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProcessesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('processes', function (Blueprint $table) {
            $table->integer('device_id')->index('device_id');
            $table->integer('pid');
            $table->integer('vsz');
            $table->integer('rss');
            $table->string('cputime', 12);
            $table->string('user', 50);
            $table->text('command', 65535);
        });

        \DB::statement("ALTER TABLE `processes` CHANGE `pid` `pid` int(255) NOT NULL ;");
        \DB::statement("ALTER TABLE `processes` CHANGE `vsz` `vsz` int(255) NOT NULL ;");
        \DB::statement("ALTER TABLE `processes` CHANGE `rss` `rss` int(255) NOT NULL ;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('processes');
    }
}
