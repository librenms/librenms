<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateApplicationMetricsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('application_metrics', function (Blueprint $table) {
            $table->unsignedInteger('app_id');
            $table->string('metric', 32);
            $table->double('value')->nullable();
            $table->double('value_prev')->nullable();
            $table->unique(['app_id', 'metric']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('application_metrics');
    }
}
