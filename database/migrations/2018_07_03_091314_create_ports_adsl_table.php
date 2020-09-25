<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePortsAdslTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ports_adsl', function (Blueprint $table) {
            $table->unsignedInteger('port_id')->unique();
            $table->timestamp('port_adsl_updated')->useCurrent();
            $table->string('adslLineCoding', 8);
            $table->string('adslLineType', 16);
            $table->string('adslAtucInvVendorID', 8);
            $table->string('adslAtucInvVersionNumber', 8);
            $table->decimal('adslAtucCurrSnrMgn', 5, 1);
            $table->decimal('adslAtucCurrAtn', 5, 1);
            $table->decimal('adslAtucCurrOutputPwr', 5, 1);
            $table->integer('adslAtucCurrAttainableRate');
            $table->integer('adslAtucChanCurrTxRate');
            $table->string('adslAturInvSerialNumber', 8);
            $table->string('adslAturInvVendorID', 8);
            $table->string('adslAturInvVersionNumber', 8);
            $table->integer('adslAturChanCurrTxRate');
            $table->decimal('adslAturCurrSnrMgn', 5, 1);
            $table->decimal('adslAturCurrAtn', 5, 1);
            $table->decimal('adslAturCurrOutputPwr', 5, 1);
            $table->integer('adslAturCurrAttainableRate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ports_adsl');
    }
}
