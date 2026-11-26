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
            $table->unsignedInteger('isisCircLevelMetric')->nullable()->after('isisCircAdminState');
            $table->unsignedInteger('isisCircLevelWideMetric')->nullable()->after('isisCircLevelMetric');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropColumns('isis_adjacencies', ['isisCircLevelMetric', 'isisCircLevelWideMetric']);
    }
};
