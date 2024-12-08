<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('plugins', function (Blueprint $table) {
            $table->integer('version')->default(1);
            $table->longText('settings')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('plugins', function (Blueprint $table) {
            $table->dropColumn(['version', 'settings']);
        });
    }
};
