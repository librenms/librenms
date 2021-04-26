<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBillPortCountersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bill_port_counters', function (Blueprint $table) {
            $table->unsignedInteger('port_id');
            $table->timestamp('timestamp')->useCurrent();
            $table->bigInteger('in_counter')->nullable();
            $table->bigInteger('in_delta')->default(0);
            $table->bigInteger('out_counter')->nullable();
            $table->bigInteger('out_delta')->default(0);
            $table->unsignedInteger('bill_id');
            $table->primary(['port_id', 'bill_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('bill_port_counters');
    }
}
