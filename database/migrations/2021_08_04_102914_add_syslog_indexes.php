<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSyslogIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('syslog', function (Blueprint $table) {
            $table->index(['device_id', 'program']);
            $table->index(['device_id', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('syslog', function (Blueprint $table) {
            $table->dropIndex(['device_id', 'program']);
            $table->dropIndex(['device_id', 'priority']);
        });
    }
}
