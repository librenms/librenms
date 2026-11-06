<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('devices_attribs')
            ->whereIn('attrib_type', [
                'override_icmp_disable',
                'ipmi_hostname',
                'ipmi_port',
                'ipmi_ciphersuite',
                'ipmi_timeout',
                'ipmi_username',
                'ipmi_password',
                'ipmi_kg_key',
                'snmp_max_repeaters',
                'snmp_max_oid',
            ])
            ->delete();
    }
};
