<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeviceGroupsAddType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('device_groups', function (Blueprint $table) {
            $table->string('desc')->nullable()->change();
            $table->string('type', 16)->default('dynamic')->after('desc');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('device_groups', function (Blueprint $table) {
            $table->string('desc')->change();
            $table->dropColumn('type');
        });
    }
}
