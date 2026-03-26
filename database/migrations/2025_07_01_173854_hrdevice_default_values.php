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
            $table->string('hrDeviceDescr')->default('')->change();
            $table->unsignedInteger('hrDeviceErrors')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hrDevice', function (Blueprint $table) {
            $table->string('hrDeviceDescr')->change();
            $table->integer('hrDeviceErrors')->change();
        });
    }
};
