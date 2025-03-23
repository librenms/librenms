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
        Schema::table('ospfv3_nbrs', function (Blueprint $table) {
            $table->dropColumn('ospfv3NbrIfInstId');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ospfv3_nbrs', function (Blueprint $table) {
            $table->integer('ospfv3NbrIfInstId')->after('ospfv3NbrIfIndex');
        });
    }
};