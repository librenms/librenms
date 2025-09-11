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
        Schema::table('ospf_instances', function (Blueprint $table) {
            $table->unsignedInteger('ospfExternLsaCount')->change();
            $table->unsignedInteger('ospfOriginateNewLsas')->change();
            $table->unsignedInteger('ospfRxNewLsas')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ospf_instances', function (Blueprint $table) {
            $table->integer('ospfExternLsaCount')->change();
            $table->integer('ospfOriginateNewLsas')->change();
            $table->integer('ospfRxNewLsas')->change();
        });
    }
};
