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
        // Move existing Shared
        DB::table('dashboards')
            ->where('access','2')
            ->update(['access' => '3']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('dashboards')
            ->where('access','3')
            ->update(['access' => '2']);
    }
};
