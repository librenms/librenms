<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePortGroupPortTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('port_group_port')) {
            Schema::create('port_group_port', function (Blueprint $table) {
                $table->unsignedInteger('port_group_id')->unsigned()->index();
                $table->unsignedInteger('port_id')->unsigned()->index();
                $table->primary(['port_group_id', 'port_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('port_group_port');
    }
}
