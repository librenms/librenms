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
        DB::table('devices')
            ->whereNull('display')
            ->update(['display' => '']);

        Schema::table('devices', function (Blueprint $table) {
            $table->string('display', 128)->default('')->change();

            if (! Schema::hasColumn('devices', 'display_template')) {
                $table->string('display_template', 128)->nullable()->after('display');
            }
        });

        DB::table('devices')
            ->where('display', '!=', '')
            ->update(['display_template' => DB::raw('display')]);
    }

    public function down(): void
    {
        DB::table('devices')->update([
            'display' => DB::raw('display_template'),
        ]);

        Schema::table('devices', function (Blueprint $table) {
            $table->string('display', 128)->nullable()->change();
            $table->dropColumn('display_template');
        });
    }
};
