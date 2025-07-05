<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use LibreNMS\Enum\MaintenanceBehavior;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('alert_schedule', function (Blueprint $table) {
            $table->integer('behavior')->default(
                MaintenanceBehavior::SKIP_ALERTS->value
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alert_schedule', function (Blueprint $table) {
            $table->dropColumn('behavior');
        });
    }
};
