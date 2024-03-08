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
        Schema::table('sensors', function (Blueprint $table) {
            $table->string('entPhysicalIndex', 64)->change();
            $table->string('entPhysicalIndex_measured', 64)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sensors', function (Blueprint $table) {
            $table->string('entPhysicalIndex', 16)->change();
            $table->string('entPhysicalIndex_measured', 16)->change();
        });
    }
};
