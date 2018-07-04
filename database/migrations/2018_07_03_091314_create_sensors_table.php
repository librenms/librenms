<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSensorsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sensors', function(Blueprint $table)
		{
			$table->integer('sensor_id', true);
			$table->boolean('sensor_deleted')->default(0);
			$table->string('sensor_class', 64)->index('sensor_class');
			$table->integer('device_id')->unsigned()->default(0)->index('sensor_host');
			$table->string('poller_type', 16)->default('snmp');
			$table->string('sensor_oid');
			$table->string('sensor_index', 128)->nullable();
			$table->string('sensor_type')->index('sensor_type');
			$table->string('sensor_descr')->nullable();
			$table->bigInteger('sensor_divisor')->default(1);
			$table->integer('sensor_multiplier')->default(1);
			$table->float('sensor_current', 10, 0)->nullable();
			$table->float('sensor_limit', 10, 0)->nullable();
			$table->float('sensor_limit_warn', 10, 0)->nullable();
			$table->float('sensor_limit_low', 10, 0)->nullable();
			$table->float('sensor_limit_low_warn', 10, 0)->nullable();
			$table->boolean('sensor_alert')->default(1);
			$table->enum('sensor_custom', array('No','Yes'))->default('No');
			$table->string('entPhysicalIndex', 16)->nullable();
			$table->string('entPhysicalIndex_measured', 16)->nullable();
			$table->timestamp('lastupdate')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->float('sensor_prev', 10, 0)->nullable();
			$table->string('user_func', 100)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('sensors');
	}

}
