<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNotificationsAttribsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('notifications_attribs', function(Blueprint $table)
		{
			$table->integer('attrib_id', true);
			$table->integer('notifications_id');
			$table->integer('user_id');
			$table->string('key')->default('');
			$table->string('value')->default('');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('notifications_attribs');
	}

}
