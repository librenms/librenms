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
        Schema::create('ipv6_nd', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('port_id');
            $table->unsignedBigInteger('device_id');
            $table->string('mac_address', 32);
            $table->string('ipv6_address', 128);
            $table->string('context_name', 128);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ipv6_nd');
    }
};
