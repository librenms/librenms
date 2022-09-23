<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBillDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bill_data', function (Blueprint $table) {
            $table->unsignedInteger('bill_id')->index();
            $table->dateTime('timestamp');
            $table->integer('period');
            $table->bigInteger('delta');
            $table->bigInteger('in_delta');
            $table->bigInteger('out_delta');
            $table->primary(['bill_id', 'timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('bill_data');
    }
}
