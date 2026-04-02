<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('state_translations', function (Blueprint $table) {
            $table->dropColumn('state_draw_graph');
        });
    }

    public function down(): void
    {
        Schema::table('state_translations', function (Blueprint $table) {
            $table->boolean('state_draw_graph')->after('state_descr');
        });
    }
};
