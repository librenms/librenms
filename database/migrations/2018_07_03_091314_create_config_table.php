<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config', function (Blueprint $table) {
            $table->increments('config_id');
            $table->string('config_name')->unique();
            $table->string('config_value', 512);
            $table->string('config_default', 512);
            $table->string('config_descr', 100);
            $table->string('config_group', 50);
            $table->integer('config_group_order')->default(0);
            $table->string('config_sub_group', 50);
            $table->integer('config_sub_group_order')->default(0);
            $table->enum('config_hidden', ['0', '1'])->default('0');
            $table->enum('config_disabled', ['0', '1'])->default('0');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('config');
    }
}
