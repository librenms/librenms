<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStorageOidToStorageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('storage', function (Blueprint $table) {
            $table->renameColumn('storage_mib', 'type');
            $table->string('storage_size_oid')->nullable()->after('storage_size');
            $table->string('storage_used_oid')->nullable()->after('storage_used');
            $table->string('storage_free_oid')->nullable()->after('storage_free');
            $table->string('storage_perc_oid')->nullable()->after('storage_perc');
            $table->dropColumn('storage_deleted');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('storage', function (Blueprint $table) {
            $table->renameColumn('type', 'storage_mib');
            $table->dropColumn(['storage_size_oid', 'storage_used_oid', 'storage_free_oid', 'storage_perc_oid']);
            $table->boolean('storage_deleted')->default(0);
        });
    }
}
