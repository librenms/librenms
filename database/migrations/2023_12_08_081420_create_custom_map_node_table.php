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
        Schema::create('custom_map_nodes', function (Blueprint $table) {
            $table->increments('custom_map_node_id');
            $table->integer('custom_map_id')->unsigned()->index();
            $table->integer('device_id')->nullable()->unsigned()->index();
            $table->string('label', 50);
            $table->string('style', 50);
            $table->string('icon', 8)->nullable();
            $table->integer('size');
            $table->string('textface', 50);
            $table->integer('textsize');
            $table->string('textcolour', 10);
            $table->string('colourbg', 10);
            $table->string('colourbdr', 10);
            $table->foreign('device_id')->references('device_id')->on('devices')->onDelete('set null');
            $table->foreign('custom_map_id')->references('custom_map_id')->on('custom_maps')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_map_nodes');
    }
};
