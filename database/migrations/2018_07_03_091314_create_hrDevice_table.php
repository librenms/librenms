<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateHrDeviceTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('hrDevice', function(Blueprint $table)
		{
			$table->integer('hrDevice_id', true);
			$table->integer('device_id')->index('device_id');
			$table->integer('hrDeviceIndex');
			$table->text('hrDeviceDescr', 65535);
			$table->text('hrDeviceType', 65535);
			$table->integer('hrDeviceErrors')->default(0);
			$table->text('hrDeviceStatus', 65535);
			$table->boolean('hrProcessorLoad')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('hrDevice');
	}

}
