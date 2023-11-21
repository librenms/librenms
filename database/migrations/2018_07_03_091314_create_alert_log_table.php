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
        Schema::create('alert_log', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('rule_id')->index();
            $table->unsignedInteger('device_id')->index();
            $table->integer('state');
            $table->binary('details')->nullable();
            $table->timestamp('time_logged')->useCurrent()->index();
        });

        if (\LibreNMS\DB\Eloquent::getDriver() == 'mysql') {
            \DB::statement('ALTER TABLE `alert_log` CHANGE `details` `details` longblob NULL ;');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('alert_log');
    }
};
