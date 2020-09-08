<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAlertTemplateMapTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alert_template_map', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('alert_templates_id');
            $table->unsignedInteger('alert_rule_id');
            $table->index(['alert_templates_id', 'alert_rule_id'], 'alert_templates_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('alert_template_map');
    }
}
