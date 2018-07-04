<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDeviceRelationshipsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('device_relationships', function(Blueprint $table)
		{
			$table->integer('parent_device_id')->unsigned()->default(0);
			$table->integer('child_device_id')->unsigned()->index('device_relationship_child_device_id_fk');
			$table->primary(['parent_device_id','child_device_id']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('device_relationships');
	}

}
