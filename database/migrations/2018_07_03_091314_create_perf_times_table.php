<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePerfTimesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('perf_times', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('type', 8)->index('type');
			$table->string('doing', 64);
			$table->integer('start');
			$table->float('duration');
			$table->integer('devices');
			$table->string('poller');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('perf_times');
	}

}
