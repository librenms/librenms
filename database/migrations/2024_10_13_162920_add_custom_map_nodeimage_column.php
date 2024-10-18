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
            $table->integer('node_image_id')->nullable()->unsigned()->index()->after('image');
            $table->foreign('node_image_id')->references('custom_map_node_image_id')->on('custom_map_node_images');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_map_nodes', function (Blueprint $table) {
            $table->dropForeign('custom_map_nodes_node_image_id_foreign');
            $table->dropColumn(['node_image_id']);
        });
    }
};
