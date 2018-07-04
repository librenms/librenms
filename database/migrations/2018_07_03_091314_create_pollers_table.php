<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePollersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pollers', function (Blueprint $table) {
            $table->integer('id');
            $table->string('poller_name');
            $table->dateTime('last_polled');
            $table->integer('devices');
            $table->float('time_taken', 10, 0);
        });

        \DB::statement("ALTER TABLE `pollers` ADD UNIQUE `id` (`id`);");
        \DB::statement("ALTER TABLE `pollers` CHANGE `id` `id` int(11) NOT NULL auto_increment;");
        \DB::statement("ALTER TABLE `pollers` ADD PRIMARY KEY (`poller_name`);");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pollers');
    }
}
