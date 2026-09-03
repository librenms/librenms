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

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Syslog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use LibreNMS\Util\IP;

/**
 * syslog.php is started once by syslog-ng and processes every message for the life of
 * that process, so this holds the caches a receiver needs rather than a global.
 */
class Processor
{
    /** How long a sender that resolves to nothing is remembered, in seconds.  0 looks it up every message. */
    private const MISS_TTL = 60;

    /** @var array<string, array{int, string, string, ?string}> minimal device info cache */
    private array $deviceInfo = [];

    /** @var array<string, int> senders that did not resolve, and when they last failed */
    private array $misses = [];

    /**
     * Parse a message and store it if it belongs to a known device.
     */
    public function process(string $line): ?Entry
    {
        $entry = $this->parseLogLine($line);

        if ($entry === null) {
            return null;
        }

        $entry = $this->parse($entry);

        if (isset($entry->device_id)) {
            $this->storeEntry($entry);
        }

        return $entry;
    }

    public function parseLogLine(string $line): ?Entry
    {
        $fields = explode('||', trim($line));

        if (count($fields) === 8) {
            return new Entry(...$fields);
        }

        return null;
    }

    /**
     * Parse a message without storing it.
     */
    public function parse(Entry $entry): Entry
    {
        if ($this->isFiltered($entry->msg)) {
            return $entry;
        }

        $entry->host = $this->resolveHost($entry->host);
        [$device_id, $hostname, $os, $os_version] = $this->deviceInfo($entry->host);

        if (! $device_id) {
            return $entry;
        }

        $entry->device_id = $device_id;

        $this->runSyslogHooks($hostname, $os, $entry);

        $entry = $this->parseMessage($os, $os_version, $entry);

        $entry->program = trim(strtoupper($entry->program));
        $entry->msg = trim($entry->msg);

        return $entry;
    }

    private function isFiltered(string $msg): bool
    {
        foreach (LibrenmsConfig::get('syslog_filter') as $pattern) {
            if (str_contains($msg, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function resolveHost(string $host): string
    {
        $host = preg_replace('/^::ffff:/', '', $host);
        $syslog_xlate = LibrenmsConfig::get('syslog_xlate');

        return empty($syslog_xlate[$host]) ? $host : $syslog_xlate[$host];
    }

    private function runSyslogHooks(string $hostname, string $os, Entry $entry): void
    {
        if (! LibrenmsConfig::get('enable_syslog_hooks')) {
            return;
        }

        $hooks = LibrenmsConfig::getOsSetting($os, 'syslog_hook');

        if (! is_array($hooks)) {
            return;
        }

        $syslogProgMsg = $entry->program . ': ' . $entry->msg;

        foreach ($hooks as $hook) {
            if (! isset($hook['script'], $hook['regex']) || ! preg_match($hook['regex'], $syslogProgMsg)) {
                continue;
            }

            shell_exec(escapeshellcmd($hook['script']) . ' ' . escapeshellarg($hostname) . ' ' . escapeshellarg($os) . ' ' . escapeshellarg($syslogProgMsg) . ' >/dev/null 2>&1 &');
        }
    }

    /**
     * Split program/msg apart based on the conventions each OS's syslog implementation uses.
     */
    private function parseMessage(string $os, ?string $os_version, Entry $entry): Entry
    {
        return match ($os) {
            'ios', 'iosxe', 'catos' => $this->parseCiscoIosMessage($entry),
            'linux' => $this->parseLinuxMessage($entry, $os_version),
            'procurve' => $this->parseProcurveMessage($entry),
            'zywall' => $this->parseZywallMessage($entry),
            default => $entry,
        };
    }

    private function parseCiscoIosMessage(Entry $entry): Entry
    {
        if (! str_contains($entry->msg, ':')) {
            // if this looks like a program (no groups of 2 or more lowercase letters), move it to program
            if (preg_match('/[(a-z)]{2,}/', $entry->msg)) {
                return $entry;
            }

            $entry->program = $entry->msg;
            $entry->msg = '';

            return $entry;
        }

        // multipart message
        $timestamp_prefix = '([*.]?[A-Z][a-z]{2} \d\d? \d\d:\d\d:\d\d(.\d\d\d)?( [A-Z]{3})?: )?';
        $program_match = '(?<program>%?[A-Za-z\d\-_]+(:[A-Z]* %[A-Z\d\-_]+)?)';
        $message_match = '(?<msg>.*)';

        if (! preg_match('/^' . $timestamp_prefix . $program_match . ': ?' . $message_match . '/', $entry->msg, $matches)) {
            return $entry;
        }

        $entry->program = $matches['program'];
        $entry->msg = $matches['msg'];

        return $entry;
    }

    /**
     * Cisco WAP200 and similar.
     */
    private function parseWap200Message(Entry $entry): Entry
    {
        if (! preg_match('#Log: \[(?P<program>.*)] - (?P<msg>.*)#', $entry->msg, $matches)) {
            return $entry;
        }

        $entry->msg = $matches['msg'];
        $entry->program = $matches['program'];

        return $entry;
    }

    private function parseLinuxMessage(Entry $entry, ?string $version): Entry
    {
        if ($version === 'Point') {
            return $this->parseWap200Message($entry);
        }

        if (! empty($entry->program)) {
            return $entry;
        }

        // pam_krb5(sshd:auth): authentication failure; logname=root uid=0 euid=0 tty=ssh ruser= rhost=123.213.132.231
        // pam_krb5[sshd:auth]: authentication failure; logname=root uid=0 euid=0 tty=ssh ruser= rhost=123.213.132.231
        if (preg_match('#^(?P<program>([^(:]+\([^)]+\)|[^\[:]+\[[^\]]+\])) ?: ?(?P<msg>.*)$#', $entry->msg, $matches)) {
            $entry->msg = $matches['msg'];
            $entry->program = $matches['program'];

            return $entry;
        }

        if (empty($entry->facility)) {
            return $entry;
        }

        // SYSLOG CONNECTION BROKEN; FD='6', SERVER='AF_INET(123.213.132.231:514)', time_reopen='60'
        // pam_krb5: authentication failure; logname=root uid=0 euid=0 tty=ssh ruser= rhost=123.213.132.231
        // fallback, better than nothing...
        $entry->program = $entry->facility;

        return $entry;
    }

    private function parseProcurveMessage(Entry $entry): Entry
    {
        if (! preg_match('/^(?P<program>[A-Za-z]+): {2}(?P<msg>.*)/', $entry->msg, $matches)) {
            return $entry;
        }

        $entry->msg = $matches['msg'] . ' [' . $entry->program . ']';
        $entry->program = $matches['program'];

        return $entry;
    }

    /**
     * Zywall sends messages without all the fields, so the offset is wrong.
     */
    private function parseZywallMessage(Entry $entry): Entry
    {
        $msg = preg_replace('/" /', '";', stripslashes($entry->program . ':' . $entry->msg));
        $fields = str_getcsv((string) $msg, ';', escape: '\\');

        $entry->program = '';

        foreach ($fields as $field) {
            [$var, $val] = array_pad(explode('=', (string) $field, 2), 2, null);

            if ($var === 'cat') {
                $entry->program = str_replace('"', '', (string) $val);
            }
        }

        $entry->msg = implode(' ', $fields);

        return $entry;
    }

    public function storeEntry(Entry $entry): void
    {
        try {
            Syslog::query()->insert([
                'device_id' => $entry->device_id,
                // program is varchar(32), so trim it rather than lose the message
                'program' => mb_substr($entry->program, 0, 32),
                'facility' => $entry->facility,
                'priority' => $entry->priority,
                'level' => $entry->level,
                'tag' => $entry->tag,
                'msg' => $entry->msg,
                'timestamp' => $entry->timestamp,
            ]);
        } catch (QueryException $e) {
            Log::error("Failed to store syslog message from $entry->host: " . $e->getMessage());
        }
    }

    /**
     * @return array{int, string, string, ?string}
     */
    private function deviceInfo(string $host): array
    {
        if (isset($this->deviceInfo[$host])) {
            return $this->deviceInfo[$host];
        }

        if (isset($this->misses[$host]) && (now()->timestamp - $this->misses[$host]) < self::MISS_TTL) {
            return [0, $host, 'generic', null];
        }

        $ip = IP::parse($host, true);
        /** @var Device|null $deviceInfo */
        $deviceInfo = Device::query()
            ->where('hostname', $host)
            ->orWhere('sysName', $host)
            ->when($ip, fn (Builder $q) => $q->orWhere(fn (Builder $q) => $q->hasIp($ip)))
            ->first(['device_id', 'hostname', 'os', 'version']);

        if ($deviceInfo === null) {
            $this->misses[$host] = now()->timestamp;

            return [0, $host, 'generic', null];
        }

        unset($this->misses[$host]);
        $this->deviceInfo[$host] = [$deviceInfo->device_id, $deviceInfo->hostname, $deviceInfo->os ?? 'generic', $deviceInfo->version];

        return $this->deviceInfo[$host];
    }
}
