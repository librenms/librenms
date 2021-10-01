<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePortGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // migration can fail when creating index if the user's SQL server isn't new enough.
        if (Schema::hasTable('port_groups')) {
            $table = Schema::getConnection()->getDoctrineSchemaManager()
                ->listTableDetails('port_groups');

            // if the table exists and the index doesn't, add the index.
            if (! $table->hasIndex('port_groups_name_unique')) {
                Schema::table('port_groups', function (Blueprint $table) {
                    $table->unique('name');
                });
            }
        } else {
            Schema::create('port_groups', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->unique();
                $table->string('desc')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('port_groups');
    }
}
