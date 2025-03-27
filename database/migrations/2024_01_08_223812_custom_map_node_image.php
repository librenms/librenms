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
        Schema::table('custom_map_nodes', function (Blueprint $table) {
            $table->string('image', 255)->default('')->after('icon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_map_nodes', function (Blueprint $table) {
            $table->dropColumn(['image']);
        });
    }
};
