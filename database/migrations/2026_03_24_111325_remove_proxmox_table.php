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
        Schema::dropIfExists('proxmox');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('proxmox', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('device_id')->default(0);
            $table->integer('vmid');
            $table->string('cluster');
            $table->string('description')->nullable();
            $table->timestamp('last_seen')->useCurrent();
            $table->unique(['cluster', 'vmid']);
        });
    }
};
