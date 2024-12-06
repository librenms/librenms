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
            $table->dropColumn(['background_suffix', 'background_version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_maps', function (Blueprint $table) {
            $table->string('background_suffix', 10)->nullable()->after('legend_hide_overspeed');
            $table->integer('background_version')->unsigned()->after('background_suffix');
        });
    }
};
