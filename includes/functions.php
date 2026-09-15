<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Models\StateTranslation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

function device_discovery_trigger($id)
{
    if (App::runningInConsole() === false) {
        ignore_user_abort(true);
        set_time_limit(0);
    }

    $update = dbUpdate(['last_discovered' => null], 'devices', '`device_id` = ?', [$id]);
    if (! empty($update) || $update == '0') {
        $message = 'Device will be rediscovered';
    } else {
        $message = 'Error rediscovering device';
    }

    return ['status' => $update, 'message' => $message];
}

function delete_device($id)
{
    $device = DeviceCache::get($id);
    if (! $device->exists) {
        return 'No such device.';
    }

    if ($device->delete()) {
        return "Removed device $device->hostname\n";
    }

    return "Failed to remove device $device->hostname";
}

function isDomainResolves($domain)
{
    if (gethostbyname($domain) != $domain) {
        return true;
    }

    $records = dns_get_record($domain);  // returns array or false

    return ! empty($records);
}

// FIXME port to LibreNMS\Util\IPv6 class
function snmp2ipv6($ipv6_snmp)
{
    // Workaround stupid Microsoft bug in Windows 2008 -- this is fixed length!
    // < fenestro> "because whoever implemented this mib for Microsoft was ignorant of RFC 2578 section 7.7 (2)"
    $ipv6 = array_slice(explode('.', (string) $ipv6_snmp), -16);
    $ipv6_2 = [];

    for ($i = 0; $i <= 15; $i++) {
        $ipv6[$i] = Str::padLeft(dechex($ipv6[$i]), 2, '0');
    }
    for ($i = 0; $i <= 15; $i += 2) {
        $ipv6_2[] = $ipv6[$i] . $ipv6[$i + 1];
    }

    return implode(':', $ipv6_2);
}

/**
 * Check if port is valid to poll.
 * Settings: empty_ifdescr, good_if, bad_if, bad_if_regexp, bad_ifname_regexp, bad_ifalias_regexp, bad_iftype, bad_ifoperstatus
 *
 * @param  array  $port
 * @param  array  $device
 * @return bool
 */
function is_port_valid($port, $device)
{
    // check empty values first
    if (empty($port['ifDescr'])) {
        // If these are all empty, we are just going to show blank names in the ui
        if (empty($port['ifAlias']) && empty($port['ifName'])) {
            Log::debug('ignored: empty ifDescr, ifAlias and ifName');

            return false;
        }

        // ifDescr should not be empty unless it is explicitly allowed
        if (! LibrenmsConfig::getOsSetting($device['os'], 'empty_ifdescr', LibrenmsConfig::get('empty_ifdescr', false))) {
            Log::debug('ignored: empty ifDescr');

            return false;
        }
    }

    $ifDescr = $port['ifDescr'];
    $ifName = $port['ifName'] ?? '';
    $ifAlias = $port['ifAlias'] ?? '';
    $ifType = $port['ifType'] ?? '';
    $ifOperStatus = $port['ifOperStatus'] ?? '';

    if (Str::contains($ifDescr, LibrenmsConfig::getOsSetting($device['os'], 'good_if', LibrenmsConfig::get('good_if')), ignoreCase: true)) {
        return true;
    }

    foreach (LibrenmsConfig::getCombined($device['os'], 'bad_if') as $bi) {
        if (Str::contains($ifDescr, $bi, ignoreCase: true)) {
            Log::debug("ignored by ifDescr: $ifDescr (matched: $bi)");

            return false;
        }
    }

    foreach (LibrenmsConfig::getCombined($device['os'], 'bad_if_regexp') as $bir) {
        if (preg_match($bir . 'i', (string) $ifDescr)) {
            Log::debug("ignored by ifDescr: $ifDescr (matched: $bir)");

            return false;
        }
    }

    foreach (LibrenmsConfig::getCombined($device['os'], 'bad_ifname_regexp') as $bnr) {
        if (preg_match($bnr . 'i', $ifName)) {
            Log::debug("ignored by ifName: $ifName (matched: $bnr)");

            return false;
        }
    }

    foreach (LibrenmsConfig::getCombined($device['os'], 'bad_ifalias_regexp') as $bar) {
        if (preg_match($bar . 'i', $ifAlias)) {
            Log::debug("ignored by ifAlias: $ifAlias (matched: $bar)");

            return false;
        }
    }

    foreach (LibrenmsConfig::getCombined($device['os'], 'bad_iftype') as $bt) {
        if (Str::contains($ifType, $bt)) {
            Log::debug("ignored by ifType: $ifType (matched: $bt )");

            return false;
        }
    }

    foreach (LibrenmsConfig::getCombined($device['os'], 'bad_ifoperstatus') as $bos) {
        if (Str::contains($ifOperStatus, $bos)) {
            Log::debug("ignored by ifOperStatus: $ifOperStatus (matched: $bos)");

            return false;
        }
    }

    return true;
}

/**
 * Try to fill in data for ifDescr, ifName, and ifAlias if devices do not provide them.
 * Will not fill ifAlias if the user has overridden it
 * Also trims the data
 *
 * @param  array  $port
 * @param  array  $device
 */
function port_fill_missing_and_trim(&$port, $device)
{
    $port['ifDescr'] = isset($port['ifDescr']) ? trim($port['ifDescr']) : null;
    $port['ifAlias'] = isset($port['ifAlias']) ? trim($port['ifAlias']) : null;
    $port['ifName'] = isset($port['ifName']) ? trim($port['ifName']) : null;

    // When devices do not provide data, populate with other data if available
    if (! isset($port['ifDescr']) || $port['ifDescr'] == '') {
        $port['ifDescr'] = $port['ifName'];
        Log::debug(' Using ifName as ifDescr');
    }
    $attrib = DeviceCache::get($device['device_id'] ?? null)->getAttrib('ifName:' . $port['ifName']);
    if (! empty($attrib)) {
        // ifAlias overridden by user, don't update it
        unset($port['ifAlias']);
        Log::debug(' ifAlias overriden by user');
    } elseif (! isset($port['ifAlias']) || $port['ifAlias'] == '') {
        $port['ifAlias'] = $port['ifDescr'];
        Log::debug(' Using ifDescr as ifAlias');
    }

    if (! isset($port['ifName']) || $port['ifName'] == '') {
        $port['ifName'] = $port['ifDescr'];
        Log::debug(' Using ifDescr as ifName');
    }
}

/**
 * Create a new state index.  Update translations if $states is given.
 *
 * For for backward compatibility:
 *   Returns null if $states is empty, $state_name already exists, and contains state translations
 *
 * @param  string  $state_name  the unique name for this state translation
 * @param  array  $states  array of states, each must contain keys: descr, graph, value, generic
 * @return void
 */
function create_state_index($state_name, $states = []): void
{
    app('sensor-discovery')->withStateTranslations($state_name, array_map(fn ($state) => new StateTranslation([
        'state_descr' => $state['descr'],
        'state_value' => $state['value'],
        'state_generic_value' => $state['generic'],
    ]), $states));
}

function delta_to_bits($delta, $period)
{
    return round($delta * 8 / $period, 2);
}

function hytera_h2f($number, $nd)
{
    if (strlen(str_replace(' ', '', $number)) == 4) {
        $number = \LibreNMS\Util\StringHelpers::asciiToHex($number, ' ');
    }
    $r = '';
    $y = explode(' ', (string) $number);
    foreach ($y as $z) {
        $r = $z . '' . $r;
    }

    $hex = [];
    $number = substr($r, 0, -1);
    //$number = str_replace(" ", "", $number);
    for ($i = 0; $i < strlen($number); $i++) {
        $hex[] = substr($number, $i, 1);
    }

    $dec = [];
    $hexCount = count($hex);
    for ($i = 0; $i < $hexCount; $i++) {
        $dec[] = hexdec($hex[$i]);
    }

    $binfinal = '';
    $decCount = count($dec);
    for ($i = 0; $i < $decCount; $i++) {
        $binfinal .= sprintf('%04d', decbin($dec[$i]));
    }

    $sign = substr($binfinal, 0, 1);
    $exp = substr($binfinal, 1, 8);
    $exp = bindec($exp);
    $exp -= 127;
    $scibin = substr($binfinal, 9);
    $binint = substr($scibin, 0, $exp);
    $binpoint = substr($scibin, $exp);
    $intnumber = bindec('1' . $binint);

    $tmppoint = [];
    for ($i = 0; $i < strlen($binpoint); $i++) {
        $tmppoint[] = substr($binpoint, $i, 1);
    }

    $tmppoint = array_reverse($tmppoint);
    $tpointnumber = min(number_format($tmppoint[0] / 2, strlen($binpoint), '.', ''), 1);

    $pointnumber = '';
    for ($i = 1; $i < strlen($binpoint); $i++) {
        $pointnumber = number_format($tpointnumber / 2, strlen($binpoint), '.', '');
        $tpointnumber = $tmppoint[$i + 1] . substr($pointnumber, 1);
    }

    $floatfinal = $intnumber + $pointnumber;

    if ($sign == 1) {
        $floatfinal = -$floatfinal;
    }

    return number_format($floatfinal, $nd, '.', '');
}

/**
 * Function to generate PeeringDB Cache
 */
function cache_peeringdb()
{
    if (LibrenmsConfig::get('peeringdb.enabled') !== true) {
        echo 'Peering DB integration disabled' . PHP_EOL;

        return;
    }

    // Without an API key we only look up which exchanges our own ASNs are on. Crawling
    // every peer at those exchanges is what actually loads the PeeringDB API, so that
    // part stays opt in. See #14598
    $api_key = LibrenmsConfig::get('peeringdb.api_key');

    // We cache for 71 hours. Track the sync itself rather than the contents of pdb_ix,
    // otherwise installs whose ASNs return no data re-query PeeringDB on every daily run.
    if (Cache::has('peeringdb.last_sync')) {
        echo 'Cached PeeringDB data found.....' . PHP_EOL;

        return;
    }

    // Adopt the timestamps of data collected before the sync time was tracked,
    // so upgrading does not trigger a needless re-crawl.
    $previous_sync = (int) dbFetchCell('SELECT MAX(`timestamp`) FROM `pdb_ix`');
    if ($previous_sync > 0 && (time() - $previous_sync) < 255600) {
        Cache::put('peeringdb.last_sync', $previous_sync, 255600 - (time() - $previous_sync));
        echo 'Cached PeeringDB data found.....' . PHP_EOL;

        return;
    }

    $peeringdb_url = 'https://peeringdb.com/api';
    $peer_keep = [];
    $ix_keep = [];
    $incomplete = false;

    // Spread requests out so many LibreNMS installs don't hit the PeeringDB API in lockstep
    $fetch = function (string $url) use ($api_key) {
        $rand = random_int(3, 30);
        echo "Sleeping for $rand seconds before querying PeeringDB" . PHP_EOL;
        sleep($rand);

        $client = \LibreNMS\Util\Http::client();

        return ($api_key ? $client->withToken($api_key, 'Api-Key') : $client)->get($url);
    };

    // ASNs we already hold exchanges for. If one of those suddenly returns nothing it is
    // more likely a glitch than a removal, so retry it next run instead of for a week.
    $known_asns = array_map('intval', array_column(dbFetchRows('SELECT DISTINCT `asn` FROM `pdb_ix`'), 'asn'));

    // Exclude reserved, private and documentation ASN ranges
    // 23456 (AS_TRANS, RFC6793)
    // 64496 - 64511 (Documentation, RFC5398)
    // 64512 - 65534 (Private, RFC6996)
    // 65535 (Well Known, RFC7300)
    // 65536 - 65551 (Documentation, RFC5398)
    // 65552 - 131071 (IANA Reserved)
    // 4200000000 - 4294967294 (Private, RFC6996)
    // 4294967295 (Reserved, RFC7300)
    $asn_sql = 'SELECT `bgpLocalAs` FROM `devices` WHERE `disabled` = 0 AND `ignore` = 0'
        . ' AND `bgpLocalAs` > 0 AND `bgpLocalAs` != 23456'
        . ' AND `bgpLocalAs` NOT BETWEEN 64496 AND 131071'
        . ' AND `bgpLocalAs` < 4200000000 GROUP BY `bgpLocalAs`';

    foreach (dbFetchRows($asn_sql) as $as) {
        $asn = $as['bgpLocalAs'];

        // Don't ask PeeringDB about an ASN it has no data for over and over again
        if (Cache::has("peeringdb.no_data.$asn")) {
            echo "PeeringDB has no data for AS$asn, skipping" . PHP_EOL;
            continue;
        }

        $get = $fetch("$peeringdb_url/net?depth=2&asn=$asn");

        if ($get->notFound()) {
            echo "AS$asn is not in PeeringDB" . PHP_EOL;
            if (! in_array((int) $asn, $known_asns)) {
                Cache::put("peeringdb.no_data.$asn", true, 604800);
            }
            continue;
        }

        if (! $get->successful()) {
            echo "PeeringDB lookup for AS$asn failed with status {$get->status()}" . PHP_EOL;
            $incomplete = true;
            continue;
        }

        $json_data = $get->body();
        $data = json_decode($json_data);
        $ixs = $data->{'data'}[0]->{'netixlan_set'} ?? [];

        if (empty($ixs)) {
            echo "AS$asn has no exchanges in PeeringDB" . PHP_EOL;
            if (! in_array((int) $asn, $known_asns)) {
                Cache::put("peeringdb.no_data.$asn", true, 604800);
            }
            continue;
        }

        foreach ($ixs as $ix) {
            $ixid = $ix->{'ix_id'};
            $tmp_ix = dbFetchRow('SELECT * FROM `pdb_ix` WHERE `ix_id` = ? AND asn = ?', [$ixid, $asn]);
            if ($tmp_ix) {
                $pdb_ix_id = $tmp_ix['pdb_ix_id'];
                $update = ['name' => $ix->{'name'}, 'timestamp' => time()];
                dbUpdate($update, 'pdb_ix', '`ix_id` = ? AND `asn` = ?', [$ixid, $asn]);
            } else {
                $insert = [
                    'ix_id' => $ixid,
                    'name' => $ix->{'name'},
                    'asn' => $asn,
                    'timestamp' => time(),
                ];
                $pdb_ix_id = dbInsert($insert, 'pdb_ix');
            }
            $ix_keep[] = $pdb_ix_id;

            // Listing every network at an exchange is the expensive call, only key holders get it
            if (empty($api_key)) {
                continue;
            }

            $get_ix = $fetch("$peeringdb_url/netixlan?ix_id=$ixid");
            if (! $get_ix->successful()) {
                echo "PeeringDB peer lookup for IX $ixid failed with status {$get_ix->status()}" . PHP_EOL;
                $incomplete = true;
                continue;
            }

            $ix_json = $get_ix->body();
            $ix_data = json_decode($ix_json);
            $peers = $ix_data->{'data'} ?? [];
            foreach ($peers as $peer) {
                $peer_name = \LibreNMS\Util\AutonomousSystem::get($peer->{'asn'})->name();
                $tmp_peer = dbFetchRow('SELECT * FROM `pdb_ix_peers` WHERE `peer_id` = ? AND `ix_id` = ?', [$peer->{'id'}, $ixid]);
                if ($tmp_peer) {
                    $peer_keep[] = $tmp_peer['pdb_ix_peers_id'];
                    $update = [
                        'remote_asn' => $peer->{'asn'},
                        'remote_ipaddr4' => $peer->{'ipaddr4'},
                        'remote_ipaddr6' => $peer->{'ipaddr6'},
                        'name' => $peer_name,
                    ];
                    dbUpdate($update, 'pdb_ix_peers', '`pdb_ix_peers_id` = ?', [$tmp_peer['pdb_ix_peers_id']]);
                } else {
                    $peer_insert = [
                        'ix_id' => $ixid,
                        'peer_id' => $peer->{'id'},
                        'remote_asn' => $peer->{'asn'},
                        'remote_ipaddr4' => $peer->{'ipaddr4'},
                        'remote_ipaddr6' => $peer->{'ipaddr6'},
                        'name' => $peer_name,
                        'timestamp' => time(),
                    ];
                    $peer_keep[] = dbInsert($peer_insert, 'pdb_ix_peers');
                }
            }
        }
    }

    // A partial run doesn't know which rows are really gone, so leave the existing data alone
    // and let the next daily run try again.
    if ($incomplete) {
        echo 'PeeringDB sync incomplete, keeping existing data' . PHP_EOL;

        return;
    }

    // cleanup
    if (empty($peer_keep)) {
        \App\Models\PeeringdbIxPeer::query()->delete();
    } else {
        \App\Models\PeeringdbIxPeer::whereNotIn('pdb_ix_peers_id', $peer_keep)->delete();
    }
    if (empty($ix_keep)) {
        \App\Models\PeeringdbIx::query()->delete();
    } else {
        \App\Models\PeeringdbIx::whereNotIn('pdb_ix_id', $ix_keep)->delete();
    }

    Cache::put('peeringdb.last_sync', time(), 255600);
}

/**
 * @param  $device
 * @return int|null
 */
function get_device_oid_limit($device)
{
    // device takes priority
    $attrib = DeviceCache::get($device['device_id'] ?? null)->getAttrib('snmp_max_oid');
    if ($attrib !== null) {
        return $attrib;
    }

    // then os
    $os_max = LibrenmsConfig::getOsSetting($device['os'], 'snmp_max_oid', 0);
    if ($os_max > 0) {
        return $os_max;
    }

    // then global
    $global_max = LibrenmsConfig::get('snmp.max_oid', 10);

    return $global_max > 0 ? $global_max : 10;
}

/**
 * If Distributed, create a lock, then purge the mysql table
 *
 * @param  string  $table
 * @param  string  $sql
 * @return int exit code
 */
function lock_and_purge($table, $sql)
{
    $purge_name = $table . '_purge';
    $lock = Cache::lock($purge_name, 86000);
    if ($lock->get()) {
        $purge_days = LibrenmsConfig::get($purge_name);

        $name = str_replace('_', ' ', ucfirst($table));
        if (is_numeric($purge_days)) {
            if (\Illuminate\Support\Facades\DB::table($table)->whereRaw($sql, [$purge_days])->delete()) {
                echo "$name cleared for entries over $purge_days days\n";
            }
        }
        $lock->release();

        return 0;
    }

    return -1;
}

/**
 * If Distributed, create a lock, then purge the mysql table according to the sql query
 *
 * @param  string  $table
 * @param  string  $sql
 * @param  string  $msg
 * @return int exit code
 */
function lock_and_purge_query($table, $sql, $msg)
{
    $purge_name = $table . '_purge';

    $purge_duration = LibrenmsConfig::get($purge_name);
    if (! (is_numeric($purge_duration) && $purge_duration > 0)) {
        return -2;
    }
    $lock = Cache::lock($purge_name, 86000);
    if ($lock->get()) {
        if (DB::statement($sql, [$purge_duration])) {
            printf($msg, $purge_duration);
        }
        $lock->release();

        return 0;
    }

    return -1;
}
