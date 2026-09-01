<?php

/**
 * Processor.php
 *
 * Turns a syslog message into a stored record
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Jacob Wilkins
 * @author     Jacob Wilkins <jacob@9.nz>
 */

namespace LibreNMS\Syslog;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Syslog;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use LibreNMS\Util\IP;
use LibreNMS\Util\IPv6;

/**
 * syslog.php is started once by syslog-ng and processes every message for the life of
 * that process, so this holds the caches a receiver needs rather than a global.
 */
class Processor
{
    /** How long a sender that resolves to nothing is remembered, in seconds.  0 looks it up every message. */
    private const MISS_TTL = 60;

    /** @var array<string, int> sender to device_id, the part DeviceCache cannot key on */
    private array $devices = [];

    /** @var array<string, int> senders that did not resolve, and when they last failed */
    private array $misses = [];

    /**
     * Parse a message, and store it if it belongs to a known device.
     *
     * @param  array<string, mixed>  $entry
     * @return array<string, mixed>
     */
    public function process(array $entry, bool $store = true): array
    {
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
        $device = $this->device($entry['host']);
        $entry['device_id'] = $device?->device_id;
        if ($device) {
            $os = $device->os;
            $hostname = $device->hostname;

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
            } elseif ($os == 'linux' and $device->version == 'Point') {
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

            if ($store) {
                try {
                    Syslog::query()->insert([
                        'device_id' => $entry['device_id'],
                        // program is varchar(32), so trim it rather than lose the message
                        'program' => mb_substr((string) $entry['program'], 0, 32),
                        'facility' => $entry['facility'],
                        'priority' => $entry['priority'],
                        'level' => $entry['level'],
                        'tag' => $entry['tag'],
                        'msg' => $entry['msg'],
                        'timestamp' => $entry['timestamp'],
                    ]);
                } catch (QueryException $e) {
                    Log::error("Failed to store syslog message from {$entry['host']}: " . $e->getMessage());
                }
            }

            unset($os);
        }//end if

        return $entry;
    }

    /**
     * Find the device a message came from.
     *
     * A miss is remembered for MISS_TTL seconds: an unknown sender would otherwise cost a
     * lookup on every message, and a device added while the receiver runs still needs to be
     * picked up without restarting it.
     */
    private function device(string $host): ?Device
    {
        if (! isset($this->devices[$host])) {
            if (isset($this->misses[$host]) && (now()->timestamp - $this->misses[$host]) < self::MISS_TTL) {
                return null;
            }

            $device_id = $this->findDeviceId($host);

            if (! $device_id) {
                $this->misses[$host] = now()->timestamp;

                return null;
            }

            unset($this->misses[$host]);
            $this->devices[$host] = $device_id;
        }

        $device = DeviceCache::get($this->devices[$host]);

        if (! $device->exists) {
            unset($this->devices[$host]); // removed since it was cached, look it up again

            return null;
        }

        return $device;
    }

    /**
     * One query for the identifiers a sender might use: its hostname, its sysName, the
     * device address, or an address on one of its ports.
     */
    private function findDeviceId(string $host): ?int
    {
        $query = Device::where('hostname', $host)->orWhere('sysName', $host);

        if ($ip = IP::parse($host, true)) {
            $addresses = $ip instanceof IPv6 ? 'ipv6' : 'ipv4';
            $query->orWhere('ip', $ip->packed())
                ->orWhereHas($addresses, fn ($address) => $address->where($addresses . '_address', $ip->uncompressed()));
        }

        return $query->value('device_id');
    }
}
