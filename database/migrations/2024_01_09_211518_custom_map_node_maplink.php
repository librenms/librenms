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
            $table->integer('linked_custom_map_id')->nullable()->unsigned()->index()->after('device_id');
            $table->foreign('linked_custom_map_id')->references('custom_map_id')->on('custom_maps')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_map_nodes', function (Blueprint $table) {
            $table->dropForeign('custom_map_nodes_linked_custom_map_id_foreign');
            $table->dropColumn(['linked_custom_map_id']);
        });
    }
};
