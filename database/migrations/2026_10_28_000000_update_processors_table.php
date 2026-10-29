<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processors', function (Blueprint $table): void {
            $table->dropColumn('entPhysicalIndex');
            $table->integer('processor_usage')->nullable()->change();
            $table->integer('processor_perc_warn_custom')->nullable()->after('processor_perc_warn');
        });
    }

    public function down(): void
    {
        Schema::table('processors', function (Blueprint $table): void {
            $table->dropColumn('processor_perc_warn_custom');
        });

        DB::table('processors')->whereNull('processor_usage')->update(['processor_usage' => 0]);

        Schema::table('processors', function (Blueprint $table): void {
            $table->integer('processor_usage')->nullable(false)->change();
            $table->integer('entPhysicalIndex')->default(0)->after('processor_id');
        });
    }
};
