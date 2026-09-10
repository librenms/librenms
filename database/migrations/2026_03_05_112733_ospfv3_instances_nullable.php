<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ospfv3_instances', function (Blueprint $table) {
            $table->string('ospfv3RestartSupport', 32)->nullable()->change();
            $table->unsignedInteger('ospfv3RestartInterval')->nullable()->change();
            $table->string('ospfv3RestartStrictLsaChecking', 32)->nullable()->change();
            $table->string('ospfv3RestartStatus', 32)->nullable()->change();
            $table->unsignedInteger('ospfv3RestartAge')->nullable()->change();
            $table->string('ospfv3RestartExitReason', 32)->nullable()->change();
            $table->string('ospfv3StubRouterSupport', 32)->nullable()->change();
            $table->string('ospfv3StubRouterAdvertisement', 32)->nullable()->change();
            $table->unsignedInteger('ospfv3DiscontinuityTime')->nullable()->change();
            $table->unsignedInteger('ospfv3RestartTime')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ospfv3_instances', function (Blueprint $table) {
            $table->string('ospfv3RestartSupport', 32)->change();
            $table->unsignedInteger('ospfv3RestartInterval')->change();
            $table->string('ospfv3RestartStrictLsaChecking', 32)->change();
            $table->string('ospfv3RestartStatus', 32)->change();
            $table->unsignedInteger('ospfv3RestartAge')->change();
            $table->string('ospfv3RestartExitReason', 32)->change();
            $table->string('ospfv3StubRouterSupport', 32)->change();
            $table->string('ospfv3StubRouterAdvertisement', 32)->change();
            $table->unsignedInteger('ospfv3DiscontinuityTime')->change();
            $table->unsignedInteger('ospfv3RestartTime')->change();
        });
    }
};
