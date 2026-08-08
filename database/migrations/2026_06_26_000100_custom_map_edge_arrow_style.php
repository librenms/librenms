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
            $table->string('arrow_type', 16)->after('waypoints')->default('arrow');
            $table->decimal('arrow_scale', 3, 1)->after('arrow_type')->default(0.6);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_map_edges', function (Blueprint $table) {
            $table->dropColumn(['arrow_type', 'arrow_scale']);
        });
    }
};
