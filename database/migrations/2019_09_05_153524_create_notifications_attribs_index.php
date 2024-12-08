<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('notifications_attribs', function (Blueprint $table) {
            $table->index(['notifications_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('notifications_attribs', function (Blueprint $table) {
            $table->dropIndex(['notifications_id', 'user_id']);
        });
    }
};
