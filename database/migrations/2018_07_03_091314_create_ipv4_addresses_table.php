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
        Schema::create('ipv4_addresses', function (Blueprint $table) {
            $table->increments('ipv4_address_id');
            $table->string('ipv4_address', 32);
            $table->integer('ipv4_prefixlen');
            $table->string('ipv4_network_id', 32);
            $table->unsignedInteger('port_id')->index();
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
        Schema::drop('ipv4_addresses');
    }
};
