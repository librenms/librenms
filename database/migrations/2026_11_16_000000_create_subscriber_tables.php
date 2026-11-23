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
        Schema::create('subscriber_counts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('device_id');
            $table->foreign('device_id')
                ->references('device_id')
                ->on('devices')
                ->onDelete('cascade');
            $table->unsignedInteger('port_id')->nullable();
            $table->foreign('port_id')
                ->references('port_id')
                ->on('ports')
                ->onDelete('cascade');
            $table->string('session_type', 32);
            $table->unsignedInteger('sessions')->default(0);

            $table->index(['device_id', 'port_id']);
        });

        Schema::create('radius_servers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('device_id');
            $table->foreign('device_id')
                ->references('device_id')
                ->on('devices')
                ->onDelete('cascade');
            $table->string('address', 64);
            $table->unsignedInteger('port')->default(0);
            $table->string('state', 16)->nullable();
            $table->unsignedInteger('dead_count')->nullable();
            $table->unsignedBigInteger('requests')->nullable();
            $table->unsignedBigInteger('accepts')->nullable();
            $table->unsignedBigInteger('rejects')->nullable();
            $table->unsignedBigInteger('timeouts')->nullable();
            $table->unsignedInteger('round_trip_time')->nullable();

            $table->index('device_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radius_servers');
        Schema::dropIfExists('subscriber_counts');
    }
};
