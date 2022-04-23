<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMplsLspPathsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        Schema::table('mpls_lsp_paths', function (Blueprint $table) {
            $table->unsignedInteger('mplsLspPathOperMetric')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mpls_lsp_paths', function (Blueprint $table) {
            $table->unsignedInteger('mplsLspPathOperMetric')->change();
        });
    }
}
