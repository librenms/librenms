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
        Schema::dropIfExists('ipv6_networks');
    }

    /**
     * Reverse the migrations.
     *
     CREATE TABLE `ipv6_networks` (
     `ipv6_network_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     `ipv6_network` varchar(64) NOT NULL,
     `context_name` varchar(128) DEFAULT NULL,
     PRIMARY KEY (`ipv6_network_id`)
    */

    public function down(): void
    {
        Schema::create('ipv6_networks', function (Blueprint $table) {
            $table->increments('ipv6_network_id');
            $table->string('ipv6_network', 64);
            $table->string('context_name', 128)->nullable();
        });
    }
};
