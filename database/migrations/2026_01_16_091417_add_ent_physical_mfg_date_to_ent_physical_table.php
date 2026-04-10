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
        Schema::table('entPhysical', function (Blueprint $table) {
            $table->string('entPhysicalMfgDate')->nullable()->after('entPhysicalMfgName');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entPhysical', function (Blueprint $table) {
            $table->dropColumn('entPhysicalMfgDate');
        });
    }
};
