<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGraphTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('graph_types', function (Blueprint $table) {
            $table->string('graph_type', 32)->index();
            $table->string('graph_subtype', 64)->index();
            $table->string('graph_section', 32)->index();
            $table->string('graph_descr')->nullable();
            $table->integer('graph_order');
            $table->primary(['graph_type', 'graph_subtype', 'graph_section']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('graph_types');
    }
}
