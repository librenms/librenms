<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEventlogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('eventlog', function(Blueprint $table)
		{
			$table->integer('event_id', true);
			$table->integer('host')->default(0)->index('host');
			$table->integer('device_id')->index('device_id');
			$table->dateTime('datetime')->default('1970-01-02 00:00:01')->index('datetime');
			$table->text('message', 65535)->nullable();
			$table->string('type', 64)->nullable();
			$table->string('reference', 64);
			$table->string('username', 128)->nullable();
			$table->integer('severity')->nullable()->default(2);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('eventlog');
	}

}
