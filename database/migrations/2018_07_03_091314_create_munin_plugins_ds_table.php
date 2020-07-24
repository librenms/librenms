<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMuninPluginsDsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('munin_plugins_ds', function (Blueprint $table) {
            $table->unsignedInteger('mplug_id');
            $table->string('ds_name', 32);
            $table->enum('ds_type', ['COUNTER', 'ABSOLUTE', 'DERIVE', 'GAUGE'])->default('GAUGE');
            $table->string('ds_label', 64);
            $table->string('ds_cdef');
            $table->string('ds_draw', 64);
            $table->enum('ds_graph', ['no', 'yes'])->default('yes');
            $table->string('ds_info');
            $table->text('ds_extinfo');
            $table->string('ds_max', 32);
            $table->string('ds_min', 32);
            $table->string('ds_negative', 32);
            $table->string('ds_warning', 32);
            $table->string('ds_critical', 32);
            $table->string('ds_colour', 32);
            $table->text('ds_sum');
            $table->text('ds_stack');
            $table->string('ds_line', 64);
            $table->unique(['mplug_id', 'ds_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('munin_plugins_ds');
    }
}
