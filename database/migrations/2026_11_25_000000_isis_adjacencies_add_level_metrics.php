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
        Schema::table('isis_adjacencies', function (Blueprint $table): void {
            $table->unsignedInteger('isisCircLevelMetricL1')->nullable()->after('isisCircAdminState');
            $table->unsignedInteger('isisCircLevelWideMetricL1')->nullable()->after('isisCircLevelMetricL1');
            $table->unsignedInteger('isisCircLevelMetricL2')->nullable()->after('isisCircLevelWideMetricL1');
            $table->unsignedInteger('isisCircLevelWideMetricL2')->nullable()->after('isisCircLevelMetricL2');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropColumns('isis_adjacencies', [
            'isisCircLevelMetricL1',
            'isisCircLevelWideMetricL1',
            'isisCircLevelMetricL2',
            'isisCircLevelWideMetricL2',
        ]);
    }
};
