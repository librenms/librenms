<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStpTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('stp', function(Blueprint $table)
		{
			$table->integer('stp_id', true);
			$table->integer('device_id')->index('stp_host');
			$table->boolean('rootBridge');
			$table->string('bridgeAddress', 32);
			$table->string('protocolSpecification', 16);
			$table->integer('priority');
			$table->string('timeSinceTopologyChange', 32);
			$table->integer('topChanges');
			$table->string('designatedRoot', 32);
			$table->integer('rootCost');
			$table->integer('rootPort')->nullable();
			$table->integer('maxAge');
			$table->integer('helloTime');
			$table->integer('holdTime');
			$table->integer('forwardDelay');
			$table->smallInteger('bridgeMaxAge');
			$table->smallInteger('bridgeHelloTime');
			$table->smallInteger('bridgeForwardDelay');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('stp');
	}

}
