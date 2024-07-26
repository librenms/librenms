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
            $table->unsignedInteger('device_id');
            $table->unsignedInteger('port_id_high');
            $table->unsignedInteger('port_id_low');
            $table->string('ifStackStatus', 32);
            $table->unique(['device_id', 'port_id_high', 'port_id_low']);
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
