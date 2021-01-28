<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToServiceTemplatesDeviceGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_templates_device_group', function (Blueprint $table) {
            $table->foreign('service_template_id')->references('id')->on('service_templates')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('device_group_id')->references('id')->on('device_groups')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (\LibreNMS\DB\Eloquent::getDriver() !== 'sqlite') {
            Schema::table('service_templates_device_group', function (Blueprint $table) {
                $table->dropForeign('service_templates_device_group_service_template_id_foreign');
                $table->dropForeign('service_templates_device_group_device_group_id_foreign');
            });
        }
    }
}
