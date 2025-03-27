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
        Schema::create('ipv6_networks', function (Blueprint $table) {
            $table->increments('ipv6_network_id');
            $table->string('ipv6_network', 64);
            $table->string('context_name', 128)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('ipv6_networks');
    }
};
