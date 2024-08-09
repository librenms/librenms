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
        DB::statement('DROP VIEW IF EXISTS view_port_mac_links;');
        DB::statement("
            CREATE VIEW view_port_mac_links
            AS
            -- Gets a list of port IDs for devices linked by MAC address
            SELECT
              p.port_id
              ,arp.id as ipv4_mac_id
              ,rp.port_id as remote_port_id
            FROM
              ports p
              -- Find all ARP entries for this port, excluding the static entries for the local IP
              JOIN ipv4_mac arp
                ON p.port_id=arp.port_id
                  AND arp.mac_address <> p.ifPhysAddress
              -- Find all IPv4 addresses on other devices that have the same IP as the ARP entry
              JOIN ipv4_addresses a
                ON a.ipv4_address=arp.ipv4_address
              -- Find the matching port if the MAC address matches
              JOIN
                ports rp ON a.port_id=rp.port_id
                  AND arp.mac_address=rp.ifPhysAddress
              WHERE
                arp.mac_address NOT IN ('000000000000', 'ffffffffffff');
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS view_port_mac_links;');
    }
};
