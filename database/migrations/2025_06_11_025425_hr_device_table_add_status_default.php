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
            $table->string('hrDeviceDescr')->change();
            $table->string('hrDeviceStatus', 32)->default('unknown')->change();
            $table->string('hrDeviceType', 32)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hrDevice', function (Blueprint $table) {
            $table->text('hrDeviceDescr')->change();
            $table->text('hrDeviceType')->change();
            $table->text('hrDeviceStatus')->change();
        });
    }
};
