<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AllowNullableOspfColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ospf_areas', function (Blueprint $table) {
            $table->string('ospfAuthType', 64)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ospf_areas', function (Blueprint $table) {
            $table->string('ospfAuthType', 64)->nullable(false)->change();
        });
    }
}
