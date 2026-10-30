<?php

use App\Facades\LibrenmsConfig;
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

        // keep thresholds users already set, discovery did not update processor_perc_warn before this
        DB::table('processors')
            ->whereNotNull('processor_perc_warn')
            ->where('processor_perc_warn', '!=', LibrenmsConfig::get('processor_perc_warn', 75))
            ->update(['processor_perc_warn_custom' => DB::raw('processor_perc_warn')]);
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
