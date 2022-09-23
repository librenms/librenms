<?php

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPortGroupPortTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('port_group_port', function (Blueprint $table) {
            // check existing foreign key constraints because initially was in one migration
            $constraint_names = array_map(function (ForeignKeyConstraint $constraint) {
                return $constraint->getName();
            }, Schema::getConnection()->getDoctrineSchemaManager()
                ->listTableForeignKeys('port_group_port'));

            if (! in_array('port_group_port_port_group_id_foreign', $constraint_names)) {
                $table->foreign('port_group_id')->references('id')->on('port_groups')->onDelete('CASCADE');
            }

            if (! in_array('port_group_port_port_id_foreign', $constraint_names)) {
                $table->foreign('port_id')->references('port_id')->on('ports')->onDelete('CASCADE');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (\LibreNMS\DB\Eloquent::getDriver() !== 'sqlite') {
            Schema::table('port_group_port', function (Blueprint $table) {
                $table->dropForeign('port_group_port_port_group_id_foreign');
                $table->dropForeign('port_group_port_port_id_foreign');
            });
        }
    }
}
