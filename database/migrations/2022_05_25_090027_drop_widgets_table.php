<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropWidgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('widgets');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('widgets', function (Blueprint $table) {
            $table->increments('widget_id');
            $table->string('widget_title');
            $table->string('widget')->unique();
            $table->string('base_dimensions', 10);
        });
    }
}
