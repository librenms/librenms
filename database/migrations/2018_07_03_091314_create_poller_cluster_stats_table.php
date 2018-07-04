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
            $table->integer('id');
            $table->string('parent_poller')->default('');
            $table->string('poller_type')->default('');
            $table->unsignedInteger('depth');
            $table->unsignedInteger('devices');
            $table->double('worker_seconds')->unsigned();
            $table->unsignedInteger('workers');
            $table->unsignedInteger('frequency');
        });

        \DB::statement("ALTER TABLE `poller_cluster_stats` CHANGE `depth` `depth` int(11) unsigned NOT NULL ;");
        \DB::statement("ALTER TABLE `poller_cluster_stats` CHANGE `devices` `devices` int(11) unsigned NOT NULL ;");
        \DB::statement("ALTER TABLE `poller_cluster_stats` CHANGE `workers` `workers` int(11) unsigned NOT NULL ;");
        \DB::statement("ALTER TABLE `poller_cluster_stats` CHANGE `frequency` `frequency` int(11) unsigned NOT NULL ;");


        \DB::statement("ALTER TABLE `poller_cluster_stats` ADD UNIQUE `id` (`id`);");
        \DB::statement("ALTER TABLE `poller_cluster_stats` CHANGE `id` `id` int(11) NOT NULL auto_increment;");
        \DB::statement("ALTER TABLE `poller_cluster_stats` ADD PRIMARY KEY (`parent_poller`,`poller_type`);");
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
