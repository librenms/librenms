<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sensors', function (Blueprint $table): void {
            $table->string('category', 32)->nullable()->after('group');
        });
    }

    public function down(): void
    {
        Schema::table('sensors', function (Blueprint $table): void {
            $table->dropColumn('category');
        });
    }
};
