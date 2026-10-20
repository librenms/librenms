<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('proxmox_ports', 'vm_ports');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('vm_ports', 'proxmox_ports');
    }
};
