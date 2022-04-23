<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStateTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('state_translations', function (Blueprint $table) {
            $table->increments('state_translation_id');
            $table->unsignedInteger('state_index_id');
            $table->string('state_descr');
            $table->boolean('state_draw_graph');
            $table->smallInteger('state_value')->default(0);
            $table->boolean('state_generic_value');
            if (\LibreNMS\DB\Eloquent::getDriver() == 'mysql') {
                $table->timestamp('state_lastupdated')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            } else {
                $table->timestamp('state_lastupdated')->useCurrent();
            }
            $table->unique(['state_index_id', 'state_value']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('state_translations');
    }
}
