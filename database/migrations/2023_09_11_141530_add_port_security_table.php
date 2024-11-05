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
            $table->string('cpsIfPortSecurityEnable', 5)->nullable();
            $table->string('cpsIfPortSecurityStatus', 32)->nullable();
            $table->integer('cpsIfMaxSecureMacAddr')->nullable();
            $table->integer('cpsIfCurrentSecureMacAddrCount')->nullable();
            $table->string('cpsIfViolationAction', 32)->nullable();
            $table->integer('cpsIfViolationCount')->nullable();
            $table->string('cpsIfSecureLastMacAddress', 20)->nullable();
            $table->string('cpsIfStickyEnable', 5)->nullable();
            // $table->integer('cpsIfSecureLastMacAddrVlanId')->nullable();
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
