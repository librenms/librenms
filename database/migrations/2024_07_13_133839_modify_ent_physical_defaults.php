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
        Schema::table('entPhysical', function (Blueprint $table) {
            $table->text('entPhysicalDescr')->nullable()->change();
            $table->text('entPhysicalClass')->nullable()->change();
            $table->text('entPhysicalName')->nullable()->change();
            $table->text('entPhysicalModelName')->nullable()->change();
            $table->text('entPhysicalSerialNum')->nullable()->change();
            $table->integer('entPhysicalContainedIn')->default(0)->change();
            $table->integer('entPhysicalParentRelPos')->default(-1)->change();
            $table->text('entPhysicalMfgName')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entPhysical', function (Blueprint $table) {
            $table->text('entPhysicalDescr')->change();
            $table->text('entPhysicalClass')->change();
            $table->text('entPhysicalName')->change();
            $table->text('entPhysicalModelName')->change();
            $table->text('entPhysicalSerialNum')->change();
            $table->integer('entPhysicalContainedIn')->change();
            $table->integer('entPhysicalParentRelPos')->change();
            $table->text('entPhysicalMfgName')->change();
        });
    }
};
