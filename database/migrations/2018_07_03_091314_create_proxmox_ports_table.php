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
        Schema::create('proxmox_ports', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vm_id');
            $table->string('port', 10);
            $table->timestamp('last_seen')->useCurrent();
            $table->unique(['vm_id', 'port']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('proxmox_ports');
    }
};
