<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
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
    public function down(): void
    {
        Schema::drop('alert_template_map');
    }
};
