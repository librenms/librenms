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
            $table->string('label_stroke_colour', 10)->nullable()->after('text_colour');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_map_edges', function (Blueprint $table) {
            $table->dropColumn('label_stroke_colour');
        });
    }
};
