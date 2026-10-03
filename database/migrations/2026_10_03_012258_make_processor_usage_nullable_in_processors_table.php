<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allow storing an unknown usage when polling fails, instead of keeping a stale value.
     */
    public function up(): void
    {
        Schema::table('processors', function (Blueprint $table): void {
            $table->integer('processor_usage')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('processors')->whereNull('processor_usage')->update(['processor_usage' => 0]);

        Schema::table('processors', function (Blueprint $table): void {
            $table->integer('processor_usage')->nullable(false)->change();
        });
    }
};
