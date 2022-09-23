<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->increments('bill_id');
            $table->text('bill_name');
            $table->text('bill_type');
            $table->bigInteger('bill_cdr')->nullable();
            $table->integer('bill_day')->default(1);
            $table->bigInteger('bill_quota')->nullable();
            $table->bigInteger('rate_95th_in');
            $table->bigInteger('rate_95th_out');
            $table->bigInteger('rate_95th');
            $table->string('dir_95th', 3);
            $table->bigInteger('total_data');
            $table->bigInteger('total_data_in');
            $table->bigInteger('total_data_out');
            $table->bigInteger('rate_average_in');
            $table->bigInteger('rate_average_out');
            $table->bigInteger('rate_average');
            $table->dateTime('bill_last_calc');
            $table->string('bill_custid', 64);
            $table->string('bill_ref', 64);
            $table->string('bill_notes', 256);
            $table->boolean('bill_autoadded');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('bills');
    }
}
