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
        Schema::table('stp', function (Blueprint $table) {
            $table->unsignedMediumInteger('bridgeMaxAge')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stp', function (Blueprint $table) {
            $table->smallInteger('bridgeMaxAge')->change();
        });
    }
};
