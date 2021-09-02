<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PollerClusterSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('poller_cluster', function (Blueprint $table) {
            $table->boolean('poller_enabled')->nullable();
            $table->integer('poller_frequency')->nullable();
            $table->integer('poller_workers')->nullable();
            $table->integer('poller_down_retry')->nullable();
            $table->boolean('discovery_enabled')->nullable();
            $table->integer('discovery_frequency')->nullable();
            $table->integer('discovery_workers')->nullable();
            $table->boolean('services_enabled')->nullable();
            $table->integer('services_frequency')->nullable();
            $table->integer('services_workers')->nullable();
            $table->boolean('billing_enabled')->nullable();
            $table->integer('billing_frequency')->nullable();
            $table->integer('billing_calculate_frequency')->nullable();
            $table->boolean('alerting_enabled')->nullable();
            $table->integer('alerting_frequency')->nullable();
            $table->boolean('ping_enabled')->nullable();
            $table->integer('ping_frequency')->nullable();
            $table->boolean('update_enabled')->nullable();
            $table->integer('update_frequency')->nullable();
            $table->string('loglevel', 8)->nullable();
            $table->boolean('watchdog_enabled')->nullable();
            $table->string('watchdog_log')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('poller_cluster', function (Blueprint $table) {
            $table->dropColumn([
                'poller_enabled',
                'poller_frequency',
                'poller_workers',
                'poller_down_retry',
                'discovery_enabled',
                'discovery_frequency',
                'discovery_workers',
                'services_enabled',
                'services_frequency',
                'services_workers',
                'billing_enabled',
                'billing_frequency',
                'billing_calculate_frequency',
                'alerting_enabled',
                'alerting_frequency',
                'ping_enabled',
                'ping_frequency',
                'update_enabled',
                'update_frequency',
                'loglevel',
                'watchdog_enabled',
                'watchdog_log',
            ]);
        });
    }
}
