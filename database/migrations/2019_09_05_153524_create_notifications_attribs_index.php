<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNotificationsAttribsIndex extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications_attribs', function (Blueprint $table) {
            $table->index(['notifications_id','user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications_attribs', function (Blueprint $table) {
            $table->dropIndex(['notifications_id','user_id']);
        });
    }
}
