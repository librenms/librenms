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
        if (! Schema::hasColumn('bill_data', 'id')) {
            Schema::table('bill_data', function (Blueprint $table) {
                $table->dropPrimary(['bill_id', 'timestamp']);
            });

            Schema::table('bill_data', function (Blueprint $table) {
                $table->id()->first();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_data', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('bill_data', function (Blueprint $table) {
            $table->primary(['bill_id', 'timestamp']);
        });
    }
};
