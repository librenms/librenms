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
        Schema::table('vminfo', function (Blueprint $table) {
            $table->string('vmwVmGuestOS', 256)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vminfo', function (Blueprint $table) {
            $table->string('vmwVmGuestOS', 128)->change();
        });
    }
};
