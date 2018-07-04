<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUcdDiskioTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ucd_diskio', function(Blueprint $table)
		{
			$table->integer('diskio_id', true);
			$table->integer('device_id')->index('device_id_2');
			$table->integer('diskio_index');
			$table->string('diskio_descr', 32);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ucd_diskio');
	}

}
