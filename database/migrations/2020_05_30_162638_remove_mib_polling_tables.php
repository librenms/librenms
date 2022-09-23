<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveMibPollingTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('device_mibs');
        Schema::drop('device_oids');
        Schema::drop('mibdefs');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('device_mibs', function (Blueprint $table) {
            $table->unsignedInteger('device_id');
            $table->string('module');
            $table->string('mib');
            $table->string('included_by');
            if (\LibreNMS\DB\Eloquent::getDriver() == 'mysql') {
                $table->timestamp('last_modified')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            } else {
                $table->timestamp('last_modified')->useCurrent();
            }
            $table->primary(['device_id', 'module', 'mib']);
        });
        Schema::create('device_oids', function (Blueprint $table) {
            $table->unsignedInteger('device_id');
            $table->string('oid');
            $table->string('module');
            $table->string('mib');
            $table->string('object_type');
            $table->string('value')->nullable();
            $table->bigInteger('numvalue')->nullable();
            if (\LibreNMS\DB\Eloquent::getDriver() == 'mysql') {
                $table->timestamp('last_modified')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            } else {
                $table->timestamp('last_modified')->useCurrent();
            }
            $table->primary(['device_id', 'oid']);
        });
        Schema::create('mibdefs', function (Blueprint $table) {
            $table->string('module');
            $table->string('mib');
            $table->string('object_type');
            $table->string('oid');
            $table->string('syntax');
            $table->string('description')->nullable();
            $table->string('max_access')->nullable();
            $table->string('status')->nullable();
            $table->string('included_by');
            if (\LibreNMS\DB\Eloquent::getDriver() == 'mysql') {
                $table->timestamp('last_modified')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            } else {
                $table->timestamp('last_modified')->useCurrent();
            }
            $table->primary(['module', 'mib', 'object_type']);
        });
    }
}
