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
        var_dump(Schema::getColumnListing('bill_data'));
        if (! Schema::hasColumn('bill_data', 'id')) {
            var_dump(Schema::getColumnListing('bill_data'));
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
            $table->dropColumn();
        });

        Schema::table('bill_data', function (Blueprint $table) {
            $table->primary(['bill_id', 'timestamp']);
        });
    }
};
