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
            $table->boolean('reverse_arrows')->default(0)->after('node_align');
            $table->smallInteger('edge_separation')->default(10)->after('reverse_arrows');
            $table->integer('legend_x')->default(-1)->after('edge_separation');
            $table->integer('legend_y')->default(-1)->after('legend_x');
            $table->smallInteger('legend_steps')->default(7)->after('legend_y');
            $table->smallInteger('legend_font_size')->default(14)->after('legend_steps');
            $table->boolean('legend_hide_invalid')->default(0)->after('legend_font_size');
            $table->boolean('legend_hide_overspeed')->default(0)->after('legend_hide_invalid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_maps', function (Blueprint $table) {
            $table->dropColumn(['reverse_arrows', 'edge_separation', 'legend_x', 'legend_y', 'legend_steps', 'legend_steps', 'legend_hide_invalid', 'legend_hide_overspeed']);
        });
    }
};
