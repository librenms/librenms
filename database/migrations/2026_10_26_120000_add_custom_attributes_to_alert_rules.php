<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alert_rules', function (Blueprint $table): void {
            $table->mediumText('custom_attributes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('alert_rules', function (Blueprint $table): void {
            $table->dropColumn('custom_attributes');
        });
    }
};
