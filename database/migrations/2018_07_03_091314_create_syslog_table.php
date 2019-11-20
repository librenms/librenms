<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSyslogTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('syslog', function (Blueprint $table) {
            $table->unsignedInteger('device_id')->nullable()->index('device_id');
            $table->string('facility', 10)->nullable();
            $table->string('priority', 10)->nullable();
            $table->string('level', 10)->nullable();
            $table->string('tag', 10)->nullable();
            $table->timestamp('timestamp')->default(DB::raw('CURRENT_TIMESTAMP'))->index('datetime');
            $table->string('program', 32)->nullable()->index('program');
            $table->text('msg', 65535)->nullable();
            $table->bigInteger('seq', true)->unsigned();
            $table->index(['priority','level'], 'priority_level');
            $table->index(['device_id', 'timestamp'], 'device_id-timestamp');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('syslog');
    }
}
