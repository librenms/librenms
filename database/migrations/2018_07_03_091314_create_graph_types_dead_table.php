<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGraphTypesDeadTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('graph_types_dead', function(Blueprint $table)
		{
			$table->string('graph_type', 32)->index('graph_type');
			$table->string('graph_subtype', 32)->index('graph_subtype');
			$table->string('graph_section', 32)->index('graph_section');
			$table->string('graph_descr', 64);
			$table->integer('graph_order');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('graph_types_dead');
	}

}
