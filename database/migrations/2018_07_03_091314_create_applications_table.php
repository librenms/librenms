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
        Schema::create('applications', function (Blueprint $table) {
            $table->increments('app_id');
            $table->unsignedInteger('device_id');
            $table->string('app_type', 64);
            $table->string('app_state', 32)->default('UNKNOWN');
            $table->tinyInteger('discovered')->default(0);
            $table->string('app_state_prev', 32)->nullable();
            $table->string('app_status', 8);
            if (\LibreNMS\DB\Eloquent::getDriver() == 'mysql') {
                $table->timestamp('timestamp')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            } else {
                $table->timestamp('timestamp')->useCurrent();
            }
            $table->string('app_instance');
            $table->unique(['device_id', 'app_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('applications');
    }
};
