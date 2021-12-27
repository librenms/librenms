<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePortsTextFieldsToVarchar extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ports', function (Blueprint $table) {
            $table->string('ifAlias')->change();
            $table->string('ifType', 64)->change();
            $table->string('ifPhysAddress', 64)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ports', function (Blueprint $table) {
            $table->text('ifAlias')->change();
            $table->text('ifType')->change();
            $table->text('ifPhysAddress')->change();
        });
    }
}
