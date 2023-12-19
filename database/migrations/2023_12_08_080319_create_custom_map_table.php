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
        Schema::create('custom_maps', function (Blueprint $table) {
            $table->increments('custom_map_id');
            $table->string('name', 100);
            $table->string('width', 10);
            $table->string('height', 10);
            $table->string('background_suffix', 10)->nullable();
            $table->integer('background_version')->unsigned();
            $table->longText('options')->nullable();
            $table->longText('newnodeconfig');
            $table->longText('newedgeconfig');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_maps');
    }
};
