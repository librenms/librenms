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
        Schema::table('locations', function (Blueprint $table) {
            $table->decimal('lat', 10, 8)->nullable()->change();
            $table->decimal('lng', 11, 8)->nullable()->change();
        });

        Schema::table('slas', function (Blueprint $table) {
            $table->double('rtt')->nullable()->change();
        });

        Schema::table('poller_cluster_stats', function (Blueprint $table) {
            $table->double('worker_seconds')->change();
        });
    }
};
