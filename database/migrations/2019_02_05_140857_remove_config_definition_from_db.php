<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveConfigDefinitionFromDb extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('config', function (Blueprint $table) {
            $table->dropColumn([
                'config_default',
                'config_descr',
                'config_group',
                'config_group_order',
                'config_sub_group',
                'config_sub_group_order',
                'config_hidden',
                'config_disabled',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('config', function (Blueprint $table) {
            $table->string('config_default', 512)->nullable();
            $table->string('config_descr', 100)->nullable();
            $table->string('config_group', 50)->nullable();
            $table->integer('config_group_order')->default(0);
            $table->string('config_sub_group', 50)->nullable();
            $table->integer('config_sub_group_order')->default(0);
            $table->enum('config_hidden', ['0', '1'])->default('0');
            $table->enum('config_disabled', ['0', '1'])->default('0');
        });
    }
}
