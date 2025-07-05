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
        Schema::table('transceivers', function (Blueprint $table) {
            $table->string('type')->nullable()->change();
            $table->string('vendor')->nullable()->change();
            $table->string('oui')->nullable()->change();
            $table->string('model')->nullable()->change();
            $table->string('revision')->nullable()->change();
            $table->string('serial')->nullable()->change();
            $table->string('encoding')->nullable()->change();
            $table->string('cable')->nullable()->change();
            $table->string('connector')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transceivers', function (Blueprint $table) {
            $table->string('type', 128)->nullable()->change();
            $table->string('vendor', 16)->nullable()->change();
            $table->string('oui', 16)->nullable()->change();
            $table->string('model', 32)->nullable()->change();
            $table->string('revision', 16)->nullable()->change();
            $table->string('serial', 32)->nullable()->change();
            $table->string('encoding', 16)->nullable()->change();
            $table->string('cable', 16)->nullable()->change();
            $table->string('connector', 16)->nullable()->change();
        });
    }
};
