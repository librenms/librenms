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
        Schema::dropIfExists('device_stats');
        Schema::create('device_stats', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedInteger('device_id')->unique();
            $table->timestamp('ping_last_timestamp')->nullable();
            $table->float('ping_rtt_last')->unsigned()->nullable();
            $table->float('ping_rtt_prev')->unsigned()->nullable();
            $table->float('ping_rtt_avg')->unsigned()->nullable();
            $table->float('ping_rtt_diff_avg_last')->virtualAs('ping_rtt_last - ping_rtt_avg');
            $table->float('ping_rtt_diff_prev_last')->virtualAs('ping_rtt_last - ping_rtt_prev');
            $table->float('ping_loss_last')->unsigned()->nullable();
            $table->float('ping_loss_prev')->unsigned()->nullable();
            $table->float('ping_loss_avg')->unsigned()->nullable();
            $table->foreign('device_id')->references('device_id')->on('devices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_stats');
    }
};
