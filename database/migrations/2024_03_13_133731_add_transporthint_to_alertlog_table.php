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
        Schema::table('alert_log', function (Blueprint $table) {
            $table->json('transport_note')->nullable();
            $table->collation('utf8mb4_unicode_ci');
            $table->charset('utf8mb4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alert_log', function (Blueprint $table) {
            $table->dropColumn('transport_note');
            $table->collation('utf8_unicode_ci');
            $table->charset('utf8');
        });
    }
};
