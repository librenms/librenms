<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAlertLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('alert_log', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('rule_id')->index('rule_id');
			$table->integer('device_id')->index('device_id');
			$table->integer('state');
			$table->binary('details')->nullable();
			$table->timestamp('time_logged')->default(DB::raw('CURRENT_TIMESTAMP'))->index('time_logged');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('alert_log');
	}

}
