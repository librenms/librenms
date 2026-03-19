<?php

use App\Facades\LibrenmsConfig;
use Illuminate\Support\Facades\Log;
use LibreNMS\Syslog\CefParser;

function get_cache($host, $value)
{
    global $dev_cache;

    if (! isset($dev_cache[$host][$value])) {
        switch ($value) {
            case 'device_id':
                // Try by hostname (case-insensitive for sysName)
                $ip = inet_pton($host);
                if (inet_ntop($ip) === false) {
                    $dev_cache[$host]['device_id'] = dbFetchCell('SELECT `device_id` FROM devices WHERE `hostname` = ? OR LOWER(`sysName`) = LOWER(?)', [$host, $host]);
                } else {
                    $dev_cache[$host]['device_id'] = dbFetchCell('SELECT `device_id` FROM devices WHERE `hostname` = ? OR LOWER(`sysName`) = LOWER(?) OR `ip` = ?', [$host, $host, $ip]);
                }
                // If failed, try by IP
                if (! is_numeric($dev_cache[$host]['device_id'])) {
                    $dev_cache[$host]['device_id'] = dbFetchCell('SELECT `device_id` FROM `ipv4_addresses` AS A, `ports` AS I WHERE A.ipv4_address = ? AND I.port_id = A.port_id', [$host]);
                }
                break;

            case 'os':
                $dev_cache[$host]['os'] = dbFetchCell('SELECT `os` FROM devices WHERE `device_id` = ?', [get_cache($host, 'device_id')]);
                break;

            case 'version':
                $dev_cache[$host]['version'] = dbFetchCell('SELECT `version` FROM devices WHERE `device_id`= ?', [get_cache($host, 'device_id')]);
                break;

            case 'hostname':
                $dev_cache[$host]['hostname'] = dbFetchCell('SELECT `hostname` FROM devices WHERE `device_id` = ?', [get_cache($host, 'device_id')]);
                break;

            case 'sysName':
                $dev_cache[$host]['sysName'] = dbFetchCell('SELECT `sysName` FROM devices WHERE `device_id` = ?', [get_cache($host, 'device_id')]);
                break;

            default:
                return null;
        }//end switch
    }//end if

    return $dev_cache[$host][$value];
}//end get_cache()

function process_syslog($entry, $update)
{
    global $dev_cache;

    foreach (LibrenmsConfig::get('syslog_filter') as $bi) {
        if (str_contains((string) $entry['msg'], $bi)) {
            return $entry;
        }
    }

    $entry['host'] = preg_replace('/^::ffff:/', '', (string) $entry['host']);
    $syslog_xlate = LibrenmsConfig::get('syslog_xlate');
    if (! empty($syslog_xlate[$entry['host']])) {
        $entry['host'] = $syslog_xlate[$entry['host']];
    }

    // Parse CEF (Common Event Format) messages before device lookup
    $msg_to_parse = $entry['program'] . ': ' . ($entry['msg'] ?? '');
    if (CefParser::isCef($msg_to_parse)) {
        $cef = CefParser::parse($msg_to_parse);
        if ($cef !== null) {
            $entry['program'] = $cef->getProgram();
            $cef_msg = $cef->getMessage();
            $entry['msg'] = $cef_msg ? $cef->name . ': ' . $cef_msg : $cef->name;
            $cef_host = $cef->getDeviceHostname();
            if ($cef_host !== null) {
                $entry['host'] = $cef_host;
            }
        }
    }

    $entry['device_id'] = get_cache($entry['host'], 'device_id');
    $hostname = null;

    if ($entry['device_id']) {
        $os = get_cache($entry['host'], 'os');
        $hostname = get_cache($entry['host'], 'hostname');

        if (LibrenmsConfig::get('enable_syslog_hooks') && is_array(LibrenmsConfig::getOsSetting($os, 'syslog_hook'))) {
            foreach (LibrenmsConfig::getOsSetting($os, 'syslog_hook') as $v) {
                $syslogprogmsg = $entry['program'] . ': ' . $entry['msg'];
                if ((isset($v['script'])) && (isset($v['regex'])) && preg_match($v['regex'], $syslogprogmsg)) {
                    shell_exec(escapeshellcmd($v['script']) . ' ' . escapeshellarg((string) $hostname) . ' ' . escapeshellarg((string) $os) . ' ' . escapeshellarg($syslogprogmsg) . ' >/dev/null 2>&1 &');
                }
            }
        }

        if (in_array($os, ['ios', 'iosxe', 'catos'])) {
            // multipart message
            if (str_contains((string) $entry['msg'], ':')) {
                $matches = [];
                $timestamp_prefix = '([\*\.]?[A-Z][a-z]{2} \d\d? \d\d:\d\d:\d\d(.\d\d\d)?( [A-Z]{3})?: )?';
                $program_match = '(?<program>%?[A-Za-z\d\-_]+(:[A-Z]* %[A-Z\d\-_]+)?)';
                $message_match = '(?<msg>.*)';
                if (preg_match('/^' . $timestamp_prefix . $program_match . ': ?' . $message_match . '/', (string) $entry['msg'], $matches)) {
                    $entry['program'] = $matches['program'];
                    $entry['msg'] = $matches['msg'];
                }
                unset($matches);
            } else {
                // if this looks like a program (no groups of 2 or more lowercase letters), move it to program
                if (! preg_match('/[(a-z)]{2,}/', (string) $entry['msg'])) {
                    $entry['program'] = $entry['msg'];
                    unset($entry['msg']);
                }
            }
        } elseif ($os == 'linux' and get_cache($entry['host'], 'version') == 'Point') {
            // Cisco WAP200 and similar
            $matches = [];
            if (preg_match('#Log: \[(?P<program>.*)\] - (?P<msg>.*)#', (string) $entry['msg'], $matches)) {
                $entry['msg'] = $matches['msg'];
                $entry['program'] = $matches['program'];
            }

            unset($matches);
        } elseif ($os == 'linux') {
            $matches = [];
            // pam_krb5(sshd:auth): authentication failure; logname=root uid=0 euid=0 tty=ssh ruser= rhost=123.213.132.231
            // pam_krb5[sshd:auth]: authentication failure; logname=root uid=0 euid=0 tty=ssh ruser= rhost=123.213.132.231
            if (empty($entry['program']) and preg_match('#^(?P<program>([^(:]+\([^)]+\)|[^\[:]+\[[^\]]+\])) ?: ?(?P<msg>.*)$#', (string) $entry['msg'], $matches)) {
                $entry['msg'] = $matches['msg'];
                $entry['program'] = $matches['program'];
            } elseif (empty($entry['program']) and ! empty($entry['facility'])) {
                // SYSLOG CONNECTION BROKEN; FD='6', SERVER='AF_INET(123.213.132.231:514)', time_reopen='60'
                // pam_krb5: authentication failure; logname=root uid=0 euid=0 tty=ssh ruser= rhost=123.213.132.231
                // Disabled because broke this:
                // diskio.c: don't know how to handle 10 request
                // elseif($pos = strpos($entry['msg'], ';') or $pos = strpos($entry['msg'], ':')) {
                // $entry['program'] = substr($entry['msg'], 0, $pos);
                // $entry['msg'] = substr($entry['msg'], $pos+1);
                // }
                // fallback, better than nothing...
                $entry['program'] = $entry['facility'];
            }

            unset($matches);
        } elseif ($os == 'procurve') {
            $matches = [];
            if (preg_match('/^(?P<program>[A-Za-z]+): {2}(?P<msg>.*)/', (string) $entry['msg'], $matches)) {
                $entry['msg'] = $matches['msg'] . ' [' . $entry['program'] . ']';
                $entry['program'] = $matches['program'];
            }
            unset($matches);
        } elseif ($os == 'zywall') {
            // Zwwall sends messages without all the fields, so the offset is wrong
            $msg = preg_replace('/" /', '";', stripslashes($entry['program'] . ':' . $entry['msg']));
            $msg = str_getcsv((string) $msg, ';', escape: '\\');
            $entry['program'] = null;
            foreach ($msg as $param) {
                [$var, $val] = explode('=', (string) $param);
                if ($var == 'cat') {
                    $entry['program'] = str_replace('"', '', $val);
                }
            }
            $entry['msg'] = implode(' ', $msg);
        }//end if

        if (! isset($entry['program'])) {
            $entry['program'] = $entry['msg'];
            unset($entry['msg']);
        }

        $entry['program'] = strtoupper((string) $entry['program']);
        $entry = array_map(trim(...), $entry);

        if ($update) {
            dbInsert(
                [
                    'device_id' => $entry['device_id'],
                    'program' => $entry['program'],
                    'facility' => $entry['facility'],
                    'priority' => $entry['priority'],
                    'level' => $entry['level'],
                    'tag' => $entry['tag'],
                    'msg' => $entry['msg'],
                    'timestamp' => $entry['timestamp'],
                ],
                'syslog'
            );
        }

        unset($os);
    } else {
        // Log unmatched host if configured
        if (LibrenmsConfig::get('syslog_log_unmatched')) {
            Log::warning('Syslog received from unmatched host: ' . $entry['host']);
        }
    }//end if

    // Write syslog to file if configured (runs for both matched and unmatched hosts)
    $syslog_file = LibrenmsConfig::get('syslog_file');
    if (! empty($syslog_file)) {
        $timestamp = $entry['timestamp'] ?? date('Y-m-d H:i:s');
        $syslog_timestamp = date('M j H:i:s', strtotime($timestamp));
        // Use sysName if device matched, otherwise use the entry host
        $log_host = $entry['device_id'] ? (get_cache($entry['host'], 'sysName') ?: $hostname) : $entry['host'];
        $log_msg = stripslashes($entry['msg'] ?? '');

        $file_line = sprintf(
            "%s %s %s: %s\n",
            $syslog_timestamp,
            $log_host,
            $entry['program'] ?? '-',
            $log_msg
        );
        @file_put_contents($syslog_file, $file_line, FILE_APPEND | LOCK_EX);
    }

    return $entry;
}//end process_syslog()
