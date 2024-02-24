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
        Schema::table('custom_maps', function (Blueprint $table) {
            $table->smallInteger('node_align')->default(0)->after('height');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_maps', function (Blueprint $table) {
            $table->dropColumn(['node_align']);
        });
    }
};
