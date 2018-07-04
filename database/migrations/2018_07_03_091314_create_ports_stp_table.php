<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePortsStpTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ports_stp', function(Blueprint $table)
		{
			$table->integer('port_stp_id', true);
			$table->integer('device_id');
			$table->integer('port_id');
			$table->boolean('priority');
			$table->string('state', 11);
			$table->string('enable', 8);
			$table->integer('pathCost')->unsigned();
			$table->string('designatedRoot', 32);
			$table->smallInteger('designatedCost')->unsigned();
			$table->string('designatedBridge', 32);
			$table->integer('designatedPort');
			$table->integer('forwardTransitions')->unsigned();
			$table->unique(['device_id','port_id'], 'device_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ports_stp');
	}

}
