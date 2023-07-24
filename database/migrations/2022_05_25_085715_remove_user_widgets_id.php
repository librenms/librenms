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
