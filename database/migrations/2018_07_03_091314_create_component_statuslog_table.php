<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateComponentStatuslogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('component_statuslog', function (Blueprint $table) {
            $table->increments('id')->comment('ID for each log entry, unique index');
            $table->unsignedInteger('component_id')->index()->comment('id from the component table');
            $table->boolean('status')->default(0)->comment('The status that the component was changed TO');
            $table->text('message')->nullable();
            $table->timestamp('timestamp')->useCurrent()->comment('When the status of the component was changed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('component_statuslog');
    }
}
