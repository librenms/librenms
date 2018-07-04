<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDashboardsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('dashboards', function(Blueprint $table)
		{
			$table->integer('dashboard_id', true);
			$table->integer('user_id')->default(0);
			$table->string('dashboard_name');
			$table->integer('access')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('dashboards');
	}

}
