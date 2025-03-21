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
        Schema::create('custom_map_node_images', function (Blueprint $table) {
            $table->increments('custom_map_node_image_id');
            $table->timestamps();
            $table->binary('image');
            $table->string('mime');
            $table->string('version');
            $table->string('name');
        });
        try {
            DB::statement('ALTER TABLE custom_map_node_images MODIFY image MEDIUMBLOB');
        } catch (Exception $e) {
            // SQLite can store large values in a BLOB column
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_map_node_images');
    }
};
