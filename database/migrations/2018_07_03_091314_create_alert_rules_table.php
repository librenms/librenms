<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAlertRulesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alert_rules', function (Blueprint $table) {
            $table->increments('id');
            $table->text('rule', 65535);
            $table->enum('severity', array('ok','warning','critical'));
            $table->string('extra');
            $table->boolean('disabled');
            $table->string('name')->unique('name');
            $table->text('query', 65535);
            $table->text('builder', 65535);
            $table->string('proc', 80)->nullable();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('alert_rules');
    }
}
