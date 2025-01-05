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
        Schema::table('bill_history', function (Blueprint $table) {
            $table->bigInteger('bill_peak_out')->nullable()->after('traf_total');
            $table->bigInteger('bill_peak_in')->nullable()->after('bill_peak_out');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('bill_history', function (Blueprint $table) {
            $table->dropColumn(['bill_peak_in', 'bill_peak_out']);
        });
    }
};
