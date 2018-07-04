<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateServicesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('services', function(Blueprint $table)
		{
			$table->integer('service_id', true);
			$table->integer('device_id')->index('service_host');
			$table->text('service_ip', 65535);
			$table->string('service_type');
			$table->text('service_desc', 65535);
			$table->text('service_param', 65535);
			$table->boolean('service_ignore');
			$table->boolean('service_status')->default(0);
			$table->integer('service_changed')->default(0);
			$table->text('service_message', 65535);
			$table->boolean('service_disabled')->default(0);
			$table->text('service_ds', 65535);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('services');
	}

}
