<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

use App\Models\Device;
use App\Models\Eventlog;
use App\Models\StateTranslation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Enum\Severity;

/**
 * Parse cli discovery or poller modules and set config for this run
 *
 * @param  string  $type  discovery or poller
 * @param  array  $options  get_opts array (only m key is checked)
 * @return bool
 */
function parse_modules($type, $options)
{
    $override = false;

    if (! empty($options['m'])) {
        // get all modules in the correct order and disable all
        $modules = array_map(fn ($v) => false, Config::get("{$type}_modules", []));

        foreach (explode(',', $options['m']) as $module) {
            // parse submodules (only supported by some modules)
            if (Str::contains($module, '/')) {
                [$module, $submodule] = explode('/', $module, 2);
                $existing_submodules = Config::get("{$type}_submodules.$module", []);
                $existing_submodules[] = $submodule;
                Config::set("{$type}_submodules.$module", $existing_submodules);
            }

            $dir = $type == 'poller' ? 'polling' : $type;
            if (is_file("includes/$dir/$module.inc.php")) {
                $modules[$module] = true; // enable module
                $override = true;
            }
        }

        // filter disabled modules and set in global config
        Config::set("{$type}_modules", array_filter($modules));

        // display selected modules
        $modules = array_map(function ($module) use ($type) {
            $submodules = Config::get("{$type}_submodules.$module");

            return $module . ($submodules ? '(' . implode(',', $submodules) . ')' : '');
        }, array_keys(Config::get("{$type}_modules", [])));

        Log::debug('Override ' . $type . ' modules: ' . implode(', ', $modules));
    }

    return $override;
}

function logfile($string)
{
    $file = Config::get('log_file');
    $fd = fopen($file, 'a');

    if ($fd === false) {
        print_error("Error: Could not write to log file: $file");

        return;
    }

    fputs($fd, $string . "\n");
    fclose($fd);
}

function renamehost($id, $new, $source = 'console')
{
    $host = gethostbyid($id);
    $new_rrd_dir = Rrd::dirFromHost($new);

    if (is_dir($new_rrd_dir)) {
        Eventlog::log("Renaming of $host failed due to existing RRD folder for $new", $id, 'system', Severity::Error);

        return "Renaming of $host failed due to existing RRD folder for $new\n";
    }

    if (! is_dir($new_rrd_dir) && rename(Rrd::dirFromHost($host), $new_rrd_dir) === true) {
        dbUpdate(['hostname' => $new, 'ip' => null], 'devices', 'device_id=?', [$id]);
        Eventlog::log("Hostname changed -> $new ($source)", $id, 'system', Severity::Notice);

        return '';
    }

    Eventlog::log("Renaming of $host failed", $id, 'system', Severity::Error);

    return "Renaming of $host failed\n";
}

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

function match_network($nets, $ip, $first = false)
{
    $return = false;
    if (! is_array($nets)) {
        $nets = [$nets];
    }
    foreach ($nets as $net) {
        $rev = (preg_match("/^\!/", $net)) ? true : false;
        $net = preg_replace("/^\!/", '', $net);
        $ip_arr = explode('/', $net);
        $net_long = ip2long($ip_arr[0]);
        $x = ip2long($ip_arr[1]);
        $mask = long2ip($x) == $ip_arr[1] ? $x : 0xFFFFFFFF << (32 - $ip_arr[1]);
        $ip_long = ip2long($ip);
        if ($rev) {
            if (($ip_long & $mask) == ($net_long & $mask)) {
                return false;
            }
        } else {
            if (($ip_long & $mask) == ($net_long & $mask)) {
                $return = true;
            }
            if ($first && $return) {
                return true;
            }
        }
    }

    return $return;
}

// FIXME port to LibreNMS\Util\IPv6 class
function snmp2ipv6($ipv6_snmp)
{
    // Workaround stupid Microsoft bug in Windows 2008 -- this is fixed length!
    // < fenestro> "because whoever implemented this mib for Microsoft was ignorant of RFC 2578 section 7.7 (2)"
    $ipv6 = array_slice(explode('.', $ipv6_snmp), -16);
    $ipv6_2 = [];

    for ($i = 0; $i <= 15; $i++) {
        $ipv6[$i] = Str::padLeft(dechex($ipv6[$i]), 2, '0');
    }
    for ($i = 0; $i <= 15; $i += 2) {
        $ipv6_2[] = $ipv6[$i] . $ipv6[$i + 1];
    }

    return implode(':', $ipv6_2);
}

function hex2str($hex)
{
    $string = '';

    for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
        $string .= chr(hexdec(substr($hex, $i, 2)));
    }

    return $string;
}

// Convert an SNMP hex string to regular string
function snmp_hexstring($hex)
{
    return hex2str(str_replace(' ', '', str_replace(' 00', '', $hex)));
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
        if (! Config::getOsSetting($device['os'], 'empty_ifdescr', Config::get('empty_ifdescr', false))) {
            Log::debug('ignored: empty ifDescr');

            return false;
        }
    }

    $ifDescr = $port['ifDescr'];
    $ifName = $port['ifName'] ?? '';
    $ifAlias = $port['ifAlias'] ?? '';
    $ifType = $port['ifType'];
    $ifOperStatus = $port['ifOperStatus'] ?? '';

    if (Str::contains($ifDescr, Config::getOsSetting($device['os'], 'good_if', Config::get('good_if')), ignoreCase: true)) {
        return true;
    }

    foreach (Config::getCombined($device['os'], 'bad_if') as $bi) {
        if (Str::contains($ifDescr, $bi, ignoreCase: true)) {
            Log::debug("ignored by ifDescr: $ifDescr (matched: $bi)");

            return false;
        }
    }

    foreach (Config::getCombined($device['os'], 'bad_if_regexp') as $bir) {
        if (preg_match($bir . 'i', $ifDescr)) {
            Log::debug("ignored by ifDescr: $ifDescr (matched: $bir)");

            return false;
        }
    }

    foreach (Config::getCombined($device['os'], 'bad_ifname_regexp') as $bnr) {
        if (preg_match($bnr . 'i', $ifName)) {
            Log::debug("ignored by ifName: $ifName (matched: $bnr)");

            return false;
        }
    }

    foreach (Config::getCombined($device['os'], 'bad_ifalias_regexp') as $bar) {
        if (preg_match($bar . 'i', $ifAlias)) {
            Log::debug("ignored by ifAlias: $ifAlias (matched: $bar)");

            return false;
        }
    }

    foreach (Config::getCombined($device['os'], 'bad_iftype') as $bt) {
        if (Str::contains($ifType, $bt)) {
            Log::debug("ignored by ifType: $ifType (matched: $bt )");

            return false;
        }
    }

    foreach (Config::getCombined($device['os'], 'bad_ifoperstatus') as $bos) {
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

function convert_delay($delay)
{
    if (preg_match('/(\d+)([mhd]?)/', $delay, $matches)) {
        $multipliers = [
            'm' => 60,
            'h' => 3600,
            'd' => 86400,
        ];

        $multiplier = $multipliers[$matches[2]] ?? 1;

        return $matches[1] * $multiplier;
    }

    return $delay === '' ? 0 : 300;
}

function normalize_snmp_ip_address($data)
{
    // $data is received from snmpwalk, can be ipv4 xxx.xxx.xxx.xxx or ipv6 xx:xx:...:xx (16 chunks)
    // ipv4 is returned unchanged, ipv6 is returned with one ':' removed out of two, like
    //  xxxx:xxxx:...:xxxx (8 chuncks)
    return preg_replace('/([0-9a-fA-F]{2}):([0-9a-fA-F]{2})/', '\1\2', explode('%', $data, 2)[0]);
}

function fix_integer_value($value)
{
    if ($value < 0) {
        $return = 4294967296 + $value;
    } else {
        $return = $value;
    }

    return $return;
}

/**
 * Checks if the $hostname provided exists in the DB already
 */
function host_exists(string $hostname, ?string $sysName = null): bool
{
    return Device::where('hostname', $hostname)
        ->when(! empty($sysName), function ($query) use ($sysName) {
            $query->when(! Config::get('allow_duplicate_sysName'), fn ($q) => $q->orWhere('sysName', $sysName))
                  ->when(! empty(Config::get('mydomain')), fn ($q) => $q->orWhere('sysName', rtrim($sysName, '.') . '.' . Config::get('mydomain')));
        })->exists();
}

/**
 * Perform DNS lookup
 *
 * @param  array  $device  Device array from database
 * @param  string  $type  The type of record to lookup
 * @return string ip
 *
 **/
function dnslookup($device, $type = false, $return = false)
{
    if (filter_var($device['hostname'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) == true || filter_var($device['hostname'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) == true) {
        return false;
    }
    if (empty($type)) {
        // We are going to use the transport to work out the record type
        if ($device['transport'] == 'udp6' || $device['transport'] == 'tcp6') {
            $type = DNS_AAAA;
            $return = 'ipv6';
        } else {
            $type = DNS_A;
            $return = 'ip';
        }
    }
    if (empty($return)) {
        return false;
    }
    $record = dns_get_record($device['hostname'], $type);

    return $record[0][$return] ?? null;
}//end dnslookup

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
    app('sensor-discovery')->withStateTranslations($state_name, array_map(function ($state) {
        return new StateTranslation([
            'state_descr' => $state['descr'],
            'state_draw_graph' => $state['graph'],
            'state_value' => $state['value'],
            'state_generic_value' => $state['generic'],
        ]);
    }, $states));
}

function delta_to_bits($delta, $period)
{
    return round($delta * 8 / $period, 2);
}

function report_this($message)
{
    return '<h2>' . htmlentities($message) . ' Please <a href="' . htmlentities(Config::get('project_issues')) . '">report this</a> to the ' . htmlentities(Config::get('project_name')) . ' developers.</h2>';
}//end report_this()

function hytera_h2f($number, $nd)
{
    if (strlen(str_replace(' ', '', $number)) == 4) {
        $number = \LibreNMS\Util\StringHelpers::asciiToHex($number, ' ');
    }
    $r = '';
    $y = explode(' ', $number);
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

function q_bridge_bits2indices($hex_data)
{
    /* convert hex string to an array of 1-based indices of the nonzero bits
     * ie. '9a00' -> '100110100000' -> array(1, 4, 5, 7)
    */
    $hex_data = str_replace([' ', "\n"], '', $hex_data);

    // we need an even number of digits for hex2bin
    if (strlen($hex_data) % 2 === 1) {
        $hex_data = '0' . $hex_data;
    }

    $value = hex2bin($hex_data);
    $length = strlen($value);
    $indices = [];
    for ($i = 0; $i < $length; $i++) {
        $byte = ord($value[$i]);
        for ($j = 7; $j >= 0; $j--) {
            if ($byte & (1 << $j)) {
                $indices[] = 8 * $i + 8 - $j;
            }
        }
    }

    return $indices;
}

/**
 * Function to generate PeeringDB Cache
 */
function cache_peeringdb()
{
    if (Config::get('peeringdb.enabled') === true) {
        $peeringdb_url = 'https://peeringdb.com/api';
        // We cache for 71 hours
        $cached = dbFetchCell('SELECT count(*) FROM `pdb_ix` WHERE (UNIX_TIMESTAMP() - timestamp) < 255600');
        if ($cached == 0) {
            $rand = rand(3, 30);
            echo "No cached PeeringDB data found, sleeping for $rand seconds" . PHP_EOL;
            sleep($rand);
            $peer_keep = [];
            $ix_keep = [];
            // Exclude Private and reserved ASN ranges
            // 64512 - 65534 (Private)
            // 65535 (Well Known)
            // 4200000000 - 4294967294 (Private)
            // 4294967295 (Reserved)
            foreach (dbFetchRows('SELECT `bgpLocalAs` FROM `devices` WHERE `disabled` = 0 AND `ignore` = 0 AND `bgpLocalAs` > 0 AND (`bgpLocalAs` < 64512 OR `bgpLocalAs` > 65535) AND `bgpLocalAs` < 4200000000 GROUP BY `bgpLocalAs`') as $as) {
                $asn = $as['bgpLocalAs'];
                $get = \LibreNMS\Util\Http::client()->get($peeringdb_url . '/net?depth=2&asn=' . $asn);
                $json_data = $get->body();
                $data = json_decode($json_data);
                $ixs = $data->{'data'}[0]->{'netixlan_set'};
                foreach ($ixs ?? [] as $ix) {
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
                    $get_ix = \LibreNMS\Util\Http::client()->get("$peeringdb_url/netixlan?ix_id=$ixid");
                    $ix_json = $get_ix->body();
                    $ix_data = json_decode($ix_json);
                    $peers = $ix_data->{'data'};
                    foreach ($peers ?? [] as $index => $peer) {
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

            // cleanup
            if (empty($peer_keep)) {
                dbDelete('pdb_ix_peers');
            } else {
                dbDelete('pdb_ix_peers', '`pdb_ix_peers_id` NOT IN ' . dbGenPlaceholders(count($peer_keep)), $peer_keep);
            }
            if (empty($ix_keep)) {
                dbDelete('pdb_ix');
            } else {
                dbDelete('pdb_ix', '`pdb_ix_id` NOT IN ' . dbGenPlaceholders(count($ix_keep)), $ix_keep);
            }
        } else {
            echo 'Cached PeeringDB data found.....' . PHP_EOL;
        }
    } else {
        echo 'Peering DB integration disabled' . PHP_EOL;
    }
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
    $os_max = Config::getOsSetting($device['os'], 'snmp_max_oid', 0);
    if ($os_max > 0) {
        return $os_max;
    }

    // then global
    $global_max = Config::get('snmp.max_oid', 10);

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
        $purge_days = Config::get($purge_name);

        $name = str_replace('_', ' ', ucfirst($table));
        if (is_numeric($purge_days)) {
            if (dbDelete($table, $sql, [$purge_days])) {
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

    $purge_duration = Config::get($purge_name);
    if (! (is_numeric($purge_duration) && $purge_duration > 0)) {
        return -2;
    }
    $lock = Cache::lock($purge_name, 86000);
    if ($lock->get()) {
        if (dbQuery($sql, [$purge_duration])) {
            printf($msg, $purge_duration);
        }
        $lock->release();

        return 0;
    }

    return -1;
}

/**
 * Check if disk is valid to poll.
 * Settings: bad_disk_regexp
 *
 * @param  array  $disk
 * @param  array  $device
 * @return bool
 */
function is_disk_valid($disk, $device)
{
    foreach (Config::getCombined($device['os'], 'bad_disk_regexp') as $bir) {
        if (preg_match($bir . 'i', $disk['diskIODevice'])) {
            Log::debug("Ignored Disk: {$disk['diskIODevice']} (matched: $bir)");

            return false;
        }
    }

    return true;
}

/**
 * Take a BGP error code and subcode to return a string representation of it
 *
 * @params int code
 * @params int subcode
 *
 * @return string
 */
function describe_bgp_error_code($code, $subcode)
{
    // https://www.iana.org/assignments/bgp-parameters/bgp-parameters.xhtml#bgp-parameters-3

    $message = 'Unknown';

    $error_code_key = 'bgp.error_codes.' . $code;
    $error_subcode_key = 'bgp.error_subcodes.' . $code . '.' . $subcode;

    $error_code_message = __($error_code_key);
    $error_subcode_message = __($error_subcode_key);

    if ($error_subcode_message != $error_subcode_key) {
        $message = $error_code_message . ' - ' . $error_subcode_message;
    } elseif ($error_code_message != $error_code_key) {
        $message = $error_code_message;
    }

    return $message;
}
