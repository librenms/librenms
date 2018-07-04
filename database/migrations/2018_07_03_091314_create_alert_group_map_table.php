<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAlertGroupMapTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('alert_group_map', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('rule_id');
			$table->integer('group_id');
			$table->unique(['rule_id','group_id'], 'alert_group_map_rule_id_group_id_uindex');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('alert_group_map');
	}

}
