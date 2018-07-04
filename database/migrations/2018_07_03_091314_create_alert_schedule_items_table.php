<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAlertScheduleItemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('alert_schedule_items', function(Blueprint $table)
		{
			$table->integer('item_id', true);
			$table->integer('schedule_id')->index('schedule_id');
			$table->string('target');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('alert_schedule_items');
	}

}
