<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAlertTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alert_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->longText('template');
            $table->string('title')->nullable();
            $table->string('title_rec')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('alert_templates');
    }
}
