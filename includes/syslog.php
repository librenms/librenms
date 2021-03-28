<?php

use LibreNMS\Config;

function get_cache($host, $value)
{
    global $dev_cache;

    if (! isset($dev_cache[$host][$value])) {
        switch ($value) {
            case 'device_id':
                // Try by hostname
                $ip = inet_pton($host);
                if (inet_ntop($ip) === false) {
                    $dev_cache[$host]['device_id'] = dbFetchCell('SELECT `device_id` FROM devices WHERE `hostname` = ? OR `sysName` = ?', [$host, $host]);
                } else {
                    $dev_cache[$host]['device_id'] = dbFetchCell('SELECT `device_id` FROM devices WHERE `hostname` = ? OR `sysName` = ? OR `ip` = ?', [$host, $host, $ip]);
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

            default:
                return null;
        }//end switch
    }//end if

    return $dev_cache[$host][$value];
}//end get_cache()

function process_syslog($entry, $update)
{
    global $dev_cache;

    foreach (Config::get('syslog_filter') as $bi) {
        if (strpos($entry['msg'], $bi) !== false) {
            return $entry;
        }
    }

    $entry['host'] = preg_replace('/^::ffff:/', '', $entry['host']);
    if ($new_host = Config::get("syslog_xlate.{$entry['host']}")) {
        $entry['host'] = $new_host;
    }
    $entry['device_id'] = get_cache($entry['host'], 'device_id');
    if ($entry['device_id']) {
        $os = get_cache($entry['host'], 'os');
        $hostname = get_cache($entry['host'], 'hostname');

        if (Config::get('enable_syslog_hooks') && is_array(Config::getOsSetting($os, 'syslog_hook'))) {
            foreach (Config::getOsSetting($os, 'syslog_hook') as $k => $v) {
                $syslogprogmsg = $entry['program'] . ': ' . $entry['msg'];
                if ((isset($v['script'])) && (isset($v['regex'])) && ((preg_match($v['regex'], $syslogprogmsg)))) {
                    shell_exec(escapeshellcmd($v['script']) . ' ' . escapeshellarg($hostname) . ' ' . escapeshellarg($os) . ' ' . escapeshellarg($syslogprogmsg) . ' >/dev/null 2>&1 &');
                }
            }
        }

        if (in_array($os, ['ios', 'iosxe', 'catos'])) {
            // multipart message
            if (strpos($entry['msg'], ':') !== false) {
                $matches = [];
                $timestamp_prefix = '([\*\.]?[A-Z][a-z]{2} \d\d? \d\d:\d\d:\d\d(.\d\d\d)?( [A-Z]{3})?: )?';
                $program_match = '(?<program>%?[A-Za-z\d\-_]+(:[A-Z]* %[A-Z\d\-_]+)?)';
                $message_match = '(?<msg>.*)';
                if (preg_match('/^' . $timestamp_prefix . $program_match . ': ?' . $message_match . '/', $entry['msg'], $matches)) {
                    $entry['program'] = $matches['program'];
                    $entry['msg'] = $matches['msg'];
                }
                unset($matches);
            } else {
                // if this looks like a program (no groups of 2 or more lowercase letters), move it to program
                if (! preg_match('/[(a-z)]{2,}/', $entry['msg'])) {
                    $entry['program'] = $entry['msg'];
                    unset($entry['msg']);
                }
            }
        } elseif ($os == 'linux' and get_cache($entry['host'], 'version') == 'Point') {
            // Cisco WAP200 and similar
            $matches = [];
            if (preg_match('#Log: \[(?P<program>.*)\] - (?P<msg>.*)#', $entry['msg'], $matches)) {
                $entry['msg'] = $matches['msg'];
                $entry['program'] = $matches['program'];
            }

            unset($matches);
        } elseif ($os == 'linux') {
            $matches = [];
            // pam_krb5(sshd:auth): authentication failure; logname=root uid=0 euid=0 tty=ssh ruser= rhost=123.213.132.231
            // pam_krb5[sshd:auth]: authentication failure; logname=root uid=0 euid=0 tty=ssh ruser= rhost=123.213.132.231
            if (empty($entry['program']) and preg_match('#^(?P<program>([^(:]+\([^)]+\)|[^\[:]+\[[^\]]+\])) ?: ?(?P<msg>.*)$#', $entry['msg'], $matches)) {
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
            if (preg_match('/^(?P<program>[A-Za-z]+): {2}(?P<msg>.*)/', $entry['msg'], $matches)) {
                $entry['msg'] = $matches['msg'] . ' [' . $entry['program'] . ']';
                $entry['program'] = $matches['program'];
            }
            unset($matches);
        } elseif ($os == 'zywall') {
            // Zwwall sends messages without all the fields, so the offset is wrong
            $msg = preg_replace('/" /', '";', stripslashes($entry['program'] . ':' . $entry['msg']));
            $msg = str_getcsv($msg, ';');
            $entry['program'] = null;
            foreach ($msg as $param) {
                [$var, $val] = explode('=', $param);
                if ($var == 'cat') {
                    $entry['program'] = str_replace('"', '', $val);
                }
            }
            $entry['msg'] = join(' ', $msg);
        }//end if

        if (! isset($entry['program'])) {
            $entry['program'] = $entry['msg'];
            unset($entry['msg']);
        }

        $entry['program'] = strtoupper($entry['program']);
        $entry = array_map('trim', $entry);

        if ($update) {
            dbInsert(
                [
                    'device_id' => $entry['device_id'],
                    'program'   => $entry['program'],
                    'facility'  => $entry['facility'],
                    'priority'  => $entry['priority'],
                    'level'     => $entry['level'],
                    'tag'       => $entry['tag'],
                    'msg'       => $entry['msg'],
                    'timestamp' => $entry['timestamp'],
                ],
                'syslog'
            );
        }

        unset($os);
    }//end if

    return $entry;
}//end process_syslog()
