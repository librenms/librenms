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
        Schema::create('ports_stack', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('device_id');
            $table->unsignedInteger('high_ifIndex');
            $table->unsignedBigInteger('high_port_id')->nullable();
            $table->unsignedInteger('low_ifIndex');
            $table->unsignedBigInteger('low_port_id')->nullable();
            $table->string('ifStackStatus', 32);
            $table->unique(['device_id', 'high_ifIndex', 'low_ifIndex']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('ports_stack');
    }
};
