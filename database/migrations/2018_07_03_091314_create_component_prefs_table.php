<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateComponentPrefsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('component_prefs', function (Blueprint $table) {
            $table->increments('id')->comment('ID for each entry');
            $table->unsignedInteger('component')->index()->comment('id from the component table');
            $table->string('attribute')->comment('Attribute for the Component');
            $table->text('value')->comment('Value for the Component');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('component_prefs');
    }
}
