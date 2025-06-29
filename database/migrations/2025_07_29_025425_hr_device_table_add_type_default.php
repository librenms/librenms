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
        Schema::table('hrDevice', function (Blueprint $table) {
            $table->string('hrDeviceType', 32)->default('')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hrDevice', function (Blueprint $table) {
            $table->text('hrDeviceType')->change();
        });
    }
};
