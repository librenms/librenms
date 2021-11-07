<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWinrmServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('winrm_services', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('device_id');
            $table->string('service_name', 250);
            $table->string('display_name', 1000)->nullable();
            $table->integer('status')->nullable();
            $table->integer('service_type')->nullable();
            $table->boolean('alerts')->default(0);
            $table->integer('start_type')->nullable();
            $table->boolean('can_pause_and_continue')->nullable();
            $table->boolean('can_shutdown')->nullable();
            $table->boolean('can_stop')->nullable();
            $table->boolean('disabled')->default(0);
            $table->unique(['device_id', 'service_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('winrm_services');
    }
}
