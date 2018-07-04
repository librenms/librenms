<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersWidgetsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users_widgets', function(Blueprint $table)
		{
			$table->integer('user_widget_id', true);
			$table->integer('user_id');
			$table->integer('widget_id');
			$table->boolean('col');
			$table->boolean('row');
			$table->boolean('size_x');
			$table->boolean('size_y');
			$table->string('title');
			$table->boolean('refresh')->default(60);
			$table->text('settings', 65535);
			$table->integer('dashboard_id');
			$table->index(['user_id','widget_id'], 'user_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users_widgets');
	}

}
