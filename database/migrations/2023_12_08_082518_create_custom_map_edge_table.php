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
        Schema::create('custom_map_edges', function (Blueprint $table) {
            $table->increments('custom_map_edge_id');
            $table->integer('custom_map_id')->unsigned()->index();
            $table->integer('custom_map_node1_id')->unsigned()->index();
            $table->integer('custom_map_node2_id')->unsigned()->index();
            $table->integer('port_id')->unsigned()->nullable()->index();
            $table->boolean('reverse');
            $table->string('style', 50);
            $table->boolean('showpct');
            $table->string('text_face', 50);
            $table->integer('text_size');
            $table->string('text_colour', 10);
            $table->integer('mid_x');
            $table->integer('mid_y');
            $table->timestamps();
            $table->foreign('custom_map_id')->references('custom_map_id')->on('custom_maps')->onDelete('cascade');
            $table->foreign('port_id')->references('port_id')->on('ports')->onDelete('set null');
            $table->foreign('custom_map_node1_id')->references('custom_map_node_id')->on('custom_map_nodes')->onDelete('cascade');
            $table->foreign('custom_map_node2_id')->references('custom_map_node_id')->on('custom_map_nodes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_map_edges');
    }
};
