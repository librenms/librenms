<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mpls_sdp_binds', function (Blueprint $table) {
            $table->string('sdpBindRowStatus', 30)->nullable()->change();
            $table->string('sdpBindAdminStatus', 5)->nullable()->change();
            $table->string('sdpBindOperStatus', 5)->nullable()->change();
            $table->string('sdpBindType', 10)->nullable()->change();
            $table->string('sdpBindVcType', 30)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // enum columns can't be modified by using change()
        Schema::table('mpls_sdp_binds', function (Blueprint $table) {
            $table->renameColumn('sdpBindRowStatus', 'sdpBindRowStatus_old');
            $table->renameColumn('sdpBindAdminStatus', 'sdpBindAdminStatus_old');
            $table->renameColumn('sdpBindOperStatus', 'sdpBindOperStatus_old');
            $table->renameColumn('sdpBindType', 'sdpBindType_old');
            $table->renameColumn('sdpBindVcType', 'sdpBindVcType_old');
        });
        Schema::table('mpls_sdp_binds', function (Blueprint $table) {
            $table->enum('sdpBindRowStatus', ['active', 'notInService', 'notReady', 'createAndGo', 'createAndWait', 'destroy'])->nullable()->after('device_id');
            $table->enum('sdpBindAdminStatus', ['up', 'down'])->nullable()->after('sdpBindRowStatus');
            $table->enum('sdpBindOperStatus', ['up', 'down'])->nullable()->after('sdpBindAdminStatus');
            $table->enum('sdpBindType', ['spoke', 'mesh'])->nullable()->after('sdpBindLastStatusChange');
            $table->enum('sdpBindVcType', ['undef', 'ether', 'vlan', 'mirrior', 'atmSduatmCell', 'atmVcc', 'atmVpc', 'frDlci', 'ipipe', 'satopE1', 'satopT1', 'satopE3', 'satopT3', 'cesopsn', 'cesopsnCas'])->nullable()->after('sdpBindType');
            DB::table('mpls_sdp_binds')->get()->each(function ($row) {
                DB::table('mpls_sdp_binds')
                    ->where('id', $row->id)
                    ->update([
                        'sdpBindRowStatus' => $row->sdpBindRowStatus_old,
                        'sdpBindAdminStatus' => $row->sdpBindAdminStatus_old,
                        'sdpBindOperStatus' => $row->sdpBindOperStatus_old,
                        'sdpBindType' => $row->sdpBindType_old,
                        'sdpBindVcType' => $row->sdpBindVcType_old,
                    ]);
            });
            $table->dropColumn('sdpBindRowStatus_old');
            $table->dropColumn('sdpBindAdminStatus_old');
            $table->dropColumn('sdpBindOperStatus_old');
            $table->dropColumn('sdpBindType_old');
            $table->dropColumn('sdpBindVcType_old');
        });
    }
};
