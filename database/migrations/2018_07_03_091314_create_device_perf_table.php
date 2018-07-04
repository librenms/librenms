<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDevicePerfTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_perf', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('device_id')->index('device_id');
            $table->dateTime('timestamp');
            $table->float('xmt', 10, 0);
            $table->float('rcv', 10, 0);
            $table->float('loss', 10, 0);
            $table->float('min', 10, 0);
            $table->float('max', 10, 0);
            $table->float('avg', 10, 0);
        });

        \DB::statement("ALTER TABLE `device_perf` CHANGE `xmt` `xmt` float NOT NULL ;");
        \DB::statement("ALTER TABLE `device_perf` CHANGE `rcv` `rcv` float NOT NULL ;");
        \DB::statement("ALTER TABLE `device_perf` CHANGE `loss` `loss` float NOT NULL ;");
        \DB::statement("ALTER TABLE `device_perf` CHANGE `min` `min` float NOT NULL ;");
        \DB::statement("ALTER TABLE `device_perf` CHANGE `max` `max` float NOT NULL ;");
        \DB::statement("ALTER TABLE `device_perf` CHANGE `avg` `avg` float NOT NULL ;");
        \DB::statement("ALTER TABLE `device_perf` ADD INDEX `id` (`id`);");
        \DB::statement("ALTER TABLE `device_perf` DROP INDEX `PRIMARY`;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('device_perf');
    }
}
