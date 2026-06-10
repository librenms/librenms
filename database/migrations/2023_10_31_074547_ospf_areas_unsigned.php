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
        Schema::table('ospf_areas', function (Blueprint $table) {
            $table->unsignedInteger('ospfSpfRuns')->change();
            $table->unsignedInteger('ospfAreaBdrRtrCount')->change();
            $table->unsignedInteger('ospfAsBdrRtrCount')->change();
            $table->unsignedInteger('ospfAreaLsaCount')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ospf_areas', function (Blueprint $table) {
            $table->integer('ospfSpfRuns')->change();
            $table->integer('ospfAreaBdrRtrCount')->change();
            $table->integer('ospfAsBdrRtrCount')->change();
            $table->integer('ospfAreaLsaCount')->change();
        });
    }
};
