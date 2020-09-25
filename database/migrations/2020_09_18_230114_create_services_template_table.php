<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateServicesTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services_template', function (Blueprint $table) {
            $table->increments('service_template_id');
            $table->unsignedInteger('device_group_id')->index();
            $table->text('service_template_ip');
            $table->string('service_template_type');
            $table->text('service_template_desc');
            $table->text('service_template_param');
            $table->boolean('service_template_ignore');
            $table->unsignedInteger('service_template_changed')->default(0);
            $table->boolean('service_template_disabled')->default(0);
            $table->string('service_template_name');
            //$table->index(['device_group_id'],'services_template_device_group_id_index');
        });
        Schema::table('services', function (Blueprint $table) {
            $table->unsignedInteger('service_template_id')->default(0);
            $table->string('service_name')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('services_template');
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'service_template_id',
                'service_name',
            ]);
        });
    }
}
