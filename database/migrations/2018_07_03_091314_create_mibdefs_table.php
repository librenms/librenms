<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMibdefsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('mibdefs');
    }
}
