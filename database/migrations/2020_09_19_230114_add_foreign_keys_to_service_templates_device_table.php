<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('service_templates_device', function (Blueprint $table) {
            $table->foreign('service_template_id')->references('id')->on('service_templates')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('device_id')->references('device_id')->on('devices')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (\LibreNMS\DB\Eloquent::getDriver() !== 'sqlite') {
            Schema::table('service_templates_device', function (Blueprint $table) {
                $table->dropForeign('service_templates_device_service_template_id_foreign');
                $table->dropForeign('service_templates_device_device_id_foreign');
            });
        }
    }
};
