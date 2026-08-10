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
        Schema::table('mpls_saps', function (Blueprint $table) {
            $table->string('sapDescription', 160)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mpls_saps', function (Blueprint $table) {
            $table->string('sapDescription', 80)->nullable()->change();
        });
    }
};
