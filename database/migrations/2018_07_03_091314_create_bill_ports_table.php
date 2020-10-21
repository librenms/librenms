<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBillPortsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bill_ports', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('bill_id');
            $table->unsignedInteger('port_id');
            $table->boolean('bill_port_autoadded')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('bill_ports');
    }
}
