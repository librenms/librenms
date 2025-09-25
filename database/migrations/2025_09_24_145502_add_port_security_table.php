<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('port_security', function (Blueprint $table) {
            $table->id();
            $table->integer('port_id')->unsigned()->default(0)->unique();
            $table->integer('device_id')->unsigned()->default(0)->index();
            $table->string('port_security_enable', 5)->nullable();
            $table->string('status', 32)->nullable();
            $table->integer('max_addresses')->nullable();
            $table->integer('address_count')->nullable();
            $table->string('violation_action', 32)->nullable();
            $table->integer('violation_count')->nullable();
            $table->string('last_mac_address', 20)->nullable();
            $table->string('sticky_enable', 5)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('port_security');
    }
};
