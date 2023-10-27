<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('device_relationships', function (Blueprint $table) {
            $table->unsignedInteger('parent_device_id')->default(0);
            $table->unsignedInteger('child_device_id')->index();
            $table->primary(['parent_device_id', 'child_device_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('device_relationships');
    }
};
