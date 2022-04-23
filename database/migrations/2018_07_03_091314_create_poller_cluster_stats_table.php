<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePollerClusterStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('poller_cluster_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('parent_poller')->default(0);
            $table->string('poller_type', 64)->default('');
            $table->unsignedInteger('depth');
            $table->unsignedInteger('devices');
            $table->double('worker_seconds')->unsigned();
            $table->unsignedInteger('workers');
            $table->unsignedInteger('frequency');
            $table->unique(['parent_poller', 'poller_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('poller_cluster_stats');
    }
}
