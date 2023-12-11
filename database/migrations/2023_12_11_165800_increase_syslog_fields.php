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
        Schema::table('syslog', function (Blueprint $table) {
            $table->string('tag', 32)->change()->index();
            $table->string('level', 16)->change();
            $table->string('program', 32)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('syslog', function (Blueprint $table) {
            $table->string('tag', 10)->change();
            $table->string('level', 10)->change();
            $table->string('program', 10)->change();
            $table->dropIndex(['tag']);
        });
    }
};
