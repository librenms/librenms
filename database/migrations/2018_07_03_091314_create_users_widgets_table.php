<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersWidgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_widgets', function (Blueprint $table) {
            $table->increments('user_widget_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('widget_id');
            $table->tinyInteger('col');
            $table->tinyInteger('row');
            $table->tinyInteger('size_x');
            $table->tinyInteger('size_y');
            $table->string('title');
            $table->tinyInteger('refresh')->default(60);
            $table->text('settings');
            $table->unsignedInteger('dashboard_id');
            $table->index(['user_id', 'widget_id'], 'user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users_widgets');
    }
}
