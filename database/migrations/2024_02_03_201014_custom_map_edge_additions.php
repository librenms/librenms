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
            $table->boolean('showbps')->default(0)->after('showpct');
            $table->string('label', 255)->default('')->after('showbps');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_map_edges', function (Blueprint $table) {
            $table->dropColumn(['showbps', 'label']);
        });
    }
};
