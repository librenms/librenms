<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('processors', function (Blueprint $table): void {
            $table->dropColumn('entPhysicalIndex');
        });

        // Allow storing an unknown usage when polling fails, instead of keeping a stale value.
        Schema::table('processors', function (Blueprint $table): void {
            $table->integer('processor_usage')->nullable()->change();
        });

        // Store the user set warning threshold separately so discovery can keep processor_perc_warn up to date.
        // processor_perc_warn remains the effective threshold used by alert rules.
        Schema::table('processors', function (Blueprint $table): void {
            $table->integer('processor_perc_warn_custom')->nullable()->after('processor_perc_warn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processors', function (Blueprint $table): void {
            $table->dropColumn('processor_perc_warn_custom');
        });

        DB::table('processors')->whereNull('processor_usage')->update(['processor_usage' => 0]);

        Schema::table('processors', function (Blueprint $table): void {
            $table->integer('processor_usage')->nullable(false)->change();
        });

        Schema::table('processors', function (Blueprint $table): void {
            $table->integer('entPhysicalIndex')->default(0)->after('processor_id');
        });
    }
};
