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
        Schema::table('ports_adsl', function (Blueprint $table) {
            $table->string('adslLineCoding', 8)->default('')->change();
            $table->string('adslLineType', 16)->default('')->change();
            $table->string('adslAtucInvVendorID', 16)->default('')->change();
            $table->string('adslAtucInvVersionNumber', 16)->default('')->change();
            $table->decimal('adslAtucCurrSnrMgn', 5, 1)->default(0)->change();
            $table->decimal('adslAtucCurrAtn', 5, 1)->default(0)->change();
            $table->decimal('adslAtucCurrOutputPwr', 5, 1)->default(0)->change();
            $table->integer('adslAtucCurrAttainableRate')->default(0)->change();
            $table->integer('adslAtucChanCurrTxRate')->default(0)->change();
            $table->string('adslAturInvSerialNumber', 32)->default('')->change();
            $table->string('adslAturInvVendorID', 16)->default('')->change();
            $table->string('adslAturInvVersionNumber', 16)->default('')->change();
            $table->integer('adslAturChanCurrTxRate')->default(0)->change();
            $table->decimal('adslAturCurrSnrMgn', 5, 1)->default(0)->change();
            $table->decimal('adslAturCurrAtn', 5, 1)->default(0)->change();
            $table->decimal('adslAturCurrOutputPwr', 5, 1)->default(0)->change();
            $table->integer('adslAturCurrAttainableRate')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('ports_adsl', function (Blueprint $table) {
            $table->string('adslLineCoding', 8)->change();
            $table->string('adslLineType', 16)->change();
            $table->string('adslAtucInvVendorID', 8)->change();
            $table->string('adslAtucInvVersionNumber', 8)->change();
            $table->decimal('adslAtucCurrSnrMgn', 5, 1)->change();
            $table->decimal('adslAtucCurrAtn', 5, 1)->change();
            $table->decimal('adslAtucCurrOutputPwr', 5, 1)->change();
            $table->integer('adslAtucCurrAttainableRate')->change();
            $table->integer('adslAtucChanCurrTxRate')->change();
            $table->string('adslAturInvSerialNumber', 8)->change();
            $table->string('adslAturInvVendorID', 8)->change();
            $table->string('adslAturInvVersionNumber', 8)->change();
            $table->integer('adslAturChanCurrTxRate')->change();
            $table->decimal('adslAturCurrSnrMgn', 5, 1)->change();
            $table->decimal('adslAturCurrAtn', 5, 1)->change();
            $table->decimal('adslAturCurrOutputPwr', 5, 1)->change();
            $table->integer('adslAturCurrAttainableRate')->change();
        });
    }
};
