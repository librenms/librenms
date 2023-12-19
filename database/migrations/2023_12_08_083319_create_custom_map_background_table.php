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
        Schema::create('custom_map_backgrounds', function (Blueprint $table) {
            $table->increments('custom_map_background_id');
            $table->timestamps();
            $table->integer('custom_map_id')->unsigned()->index()->unique();
            $table->foreign('custom_map_id')->references('custom_map_id')->on('custom_maps')->onDelete('cascade');
        });
        DB::statement('ALTER TABLE custom_map_backgrounds ADD background_image MEDIUMBLOB');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_map_backgrounds');
    }
};
