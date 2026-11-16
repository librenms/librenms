<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * A VM in a cluster is one row, whichever node it runs on.
     */
    public function up(): void
    {
        Schema::table('vminfo', function (Blueprint $table): void {
            $table->string('cluster_id', 64)->nullable();
            $table->unique(['cluster_id', 'vm_type', 'vmwVmVMID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vminfo', function (Blueprint $table): void {
            $table->dropUnique(['cluster_id', 'vm_type', 'vmwVmVMID']);
            $table->dropColumn('cluster_id');
        });
    }
};
