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
        Schema::table('ciscoASA', function (Blueprint $table) {
            $table->bigInteger('high_alert')->default(-1)->change();
            $table->bigInteger('low_alert')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ciscoASA', function (Blueprint $table) {
            $table->bigInteger('high_alert')->change();
            $table->bigInteger('low_alert')->change();
        });
    }
};
