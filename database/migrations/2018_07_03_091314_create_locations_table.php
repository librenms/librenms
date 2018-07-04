<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLocationsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('location', 65535);
            $table->float('lat', 10, 6);
            $table->float('lng', 10, 6);
            $table->dateTime('timestamp');
        });

        \DB::statement("ALTER TABLE `locations` CHANGE `lat` `lat` float(10,6) NOT NULL ;");
        \DB::statement("ALTER TABLE `locations` CHANGE `lng` `lng` float(10,6) NOT NULL ;");
        \DB::statement("ALTER TABLE `locations` ADD INDEX `id` (`id`);");
        \DB::statement("ALTER TABLE `locations` DROP INDEX `PRIMARY`;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('locations');
    }
}
