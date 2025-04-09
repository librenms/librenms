<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('users_widgets', function (Blueprint $table) {
            $table->dropIndex('user_id');
            $table->index(['user_id'], 'user_id');
            $table->dropColumn('widget_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('users_widgets', function (Blueprint $table) {
            $table->unsignedInteger('widget_id')->after('widget');
        });
    }
};
