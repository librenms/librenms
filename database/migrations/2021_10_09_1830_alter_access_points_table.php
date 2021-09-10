<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAccessPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('access_points', function (Blueprint $table) {
            $table->dropColumn('deleted');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('access_points', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->boolean('deleted')->default(0)->index();
        });
    }
}
