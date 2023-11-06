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
        Schema::table('ospf_nbrs', function (Blueprint $table) {
            $table->unsignedInteger('ospfNbrEvents')->change();
            $table->unsignedInteger('ospfNbrLsRetransQLen')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ospf_nbrs', function (Blueprint $table) {
            $table->integer('ospfNbrEvents')->change();
            $table->integer('ospfNbrLsRetransQLen')->change();
        });
    }
};
