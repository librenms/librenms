<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('ports_vlans', function (Blueprint $table) {
            $table->tinyinteger('voice')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
         Schema::table('ports_vlans', function (Blueprint $table) {
            $table->dropColumn('voice');
        });
    }
};
