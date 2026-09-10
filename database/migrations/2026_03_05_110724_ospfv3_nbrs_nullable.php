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
        Schema::table('ospfv3_nbrs', function (Blueprint $table) {
            $table->string('ospfv3NbrRestartHelperStatus', 32)->nullable()->change();
            $table->unsignedInteger('ospfv3NbrRestartHelperAge')->nullable()->change();
            $table->string('ospfv3NbrRestartHelperExitReason', 32)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ospfv3_nbrs', function (Blueprint $table) {
            $table->string('ospfv3NbrRestartHelperStatus', 32)->change();
            $table->unsignedInteger('ospfv3NbrRestartHelperAge')->change();
            $table->string('ospfv3NbrRestartHelperExitReason', 32)->change();
        });
    }
};
