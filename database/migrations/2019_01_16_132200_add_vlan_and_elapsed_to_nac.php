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
        Schema::table('ports_nac', function (Blueprint $table) {
            $table->unsignedInteger('vlan')->nullable();
            $table->string('time_elapsed', 50)->nullable();
            $table->string('time_left', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('ports_nac', function (Blueprint $table) {
            $table->dropColumn(['vlan', 'time_elapsed']);
            $table->string('time_left', 50)->change();
        });
    }
};
