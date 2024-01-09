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
            $table->integer('custom_map_link_id')->nullable()->unsigned()->index();
            $table->foreign('custom_map_link_id')->references('custom_map_id')->on('custom_maps')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_map_nodes', function (Blueprint $table) {
            $table->dropForeign('custom_map_nodes_custom_map_link_id_foreign');
            $table->dropColumn(['custom_map_link_id']);
        });
    }
};
