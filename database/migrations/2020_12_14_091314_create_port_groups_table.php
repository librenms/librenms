<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePortGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('port_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('desc')->nullable();
        });
        Schema::create('port_group_port', function (Blueprint $table) {
            $table->unsignedInteger('port_group_id')->unsigned()->index();
            $table->unsignedInteger('port_id')->unsigned()->index();
            $table->primary(['port_group_id', 'port_id']);
            $table->foreign('port_group_id')->references('id')->on('port_groups')->onDelete('CASCADE');
            $table->foreign('port_id')->references('port_id')->on('ports')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('port_group_port');
        Schema::drop('port_groups');
    }
}
