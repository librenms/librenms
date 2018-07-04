<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateComponentStatuslogTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('component_statuslog', function (Blueprint $table) {
            $table->increments('id')->comment('ID for each log entry, unique index');
            $table->integer('component_id')->unsigned()->index('device')->comment('id from the component table');
            $table->boolean('status')->default(0)->comment('The status that the component was changed TO');
            $table->text('message', 65535)->nullable();
            $table->timestamp('timestamp')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('When the status of the component was changed');
        });

        \DB::statement("ALTER TABLE `component_statuslog` CHANGE `id` `id` int(11) unsigned NOT NULL auto_increment;");
        \DB::statement("ALTER TABLE `component_statuslog` CHANGE `component_id` `component_id` int(11) unsigned NOT NULL ;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('component_statuslog');
    }
}
