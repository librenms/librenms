<?php

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Syslog;

function get_cache($host, $value)
{
    global $dev_cache;

    // $dev_cache maps a sender (an address or a name) to a device_id; DeviceCache holds
    // the device itself. Misses are deliberately not cached, so a device added after this
    // process started is still picked up without restarting it.
    if (! isset($dev_cache[$host])) {
        $device = Device::findByIp($host)
            ?: Device::where('hostname', $host)->orWhere('sysName', $host)->first();

        if (! $device) {
            return null;
        }

        $dev_cache[$host] = $device->device_id;
    }

    $device = DeviceCache::get($dev_cache[$host]);

    return match ($value) {
        'device_id' => $device->device_id,
        'os' => $device->os,
        'version' => $device->version,
        'hostname' => $device->hostname,
        default => null,
    };
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
    $entry['device_id'] = get_cache($entry['host'], 'device_id');
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
            // insertOrIgnore() keeps the INSERT IGNORE dbInsert() used: syslog is a
            // firehose and a single malformed message must not stop the ones behind it
            Syslog::query()->insertOrIgnore([
                'device_id' => $entry['device_id'],
                'program' => $entry['program'],
                'facility' => $entry['facility'],
                'priority' => $entry['priority'],
                'level' => $entry['level'],
                'tag' => $entry['tag'],
                'msg' => $entry['msg'],
                'timestamp' => $entry['timestamp'],
            ]);
        }

        unset($os);
    }//end if

    return $entry;
}//end process_syslog()
