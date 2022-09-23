<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->increments('service_id');
            $table->unsignedInteger('device_id')->index();
            $table->text('service_ip');
            $table->string('service_type');
            $table->text('service_desc');
            $table->text('service_param');
            $table->boolean('service_ignore');
            $table->tinyInteger('service_status')->default(0);
            $table->unsignedInteger('service_changed')->default(0);
            $table->text('service_message');
            $table->boolean('service_disabled')->default(0);
            $table->text('service_ds');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('services');
    }
}
