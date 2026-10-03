<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Store the user set warning threshold separately so discovery can keep processor_perc_warn up to date.
     * processor_perc_warn remains the effective threshold used by alert rules.
     */
    public function up(): void
    {
        Schema::table('processors', function (Blueprint $table): void {
            $table->integer('processor_perc_warn_custom')->nullable()->after('processor_perc_warn');
        });
    }

    public function down(): void
    {
        Schema::table('processors', function (Blueprint $table): void {
            $table->dropColumn('processor_perc_warn_custom');
        });
    }
};
