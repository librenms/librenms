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
        Schema::table('custom_map_edges', function (Blueprint $table) {
            $table->string('text_align', 16)->after('text_colour')->default('horizontal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_map_edges', function (Blueprint $table) {
            $table->dropColumn(['text_align']);
        });
    }
};
