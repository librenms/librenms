<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePollerClusterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('poller_cluster', function (Blueprint $table) {
            $table->boolean('winrmpoller_enabled')->nullable();
            $table->integer('winrmpoller_workers')->nullable();
            $table->integer('winrmpoller_frequency')->nullable();
            $table->boolean('winrmdiscovery_enabled')->nullable();
            $table->integer('winrmdiscovery_workers')->nullable();
            $table->integer('winrmdiscovery_frequency')->nullable();
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
                'winrmpoller_enabled',
                'winrmpoller_workers',
                'winrmpoller_frequency',
                'winrmdiscovery_enabled',
                'winrmdiscovery_workers',
                'winrmdiscovery_frequency',
            ]);
        });
    }
}
