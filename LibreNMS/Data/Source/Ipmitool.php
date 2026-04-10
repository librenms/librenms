<?php

/**
 * IpmitoolCommand.php
 *
 * -Description-
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Source;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use LibreNMS\Exceptions\IpmiConnectionFailed;

class Ipmitool
{
    private readonly string $binary;
    private readonly string $hostname;
    private readonly int $port;
    private readonly string $username;
    private readonly string $password;
    private readonly ?string $kg_key;
    private readonly ?int $ciphersuite;
    private readonly int $timeout;
    private ?string $type;

    public function __construct(
        private readonly Device $device,
    ) {
        $this->binary = LibrenmsConfig::get('ipmitool', 'ipmitool');
        $this->hostname = $device->getAttrib('ipmi_hostname', $device->hostname);
        $this->port = filter_var($device->getAttrib('ipmi_port'), FILTER_VALIDATE_INT) ?: 0;
        $this->username = $device->getAttrib('ipmi_username', '');
        $this->password = $device->getAttrib('ipmi_password', '');
        $this->kg_key = $device->getAttrib('ipmi_kg_key');
        $this->ciphersuite = $device->getAttrib('ipmi_ciphersuite');
        $this->timeout = filter_var($device->getAttrib('ipmi_timeout'), FILTER_VALIDATE_INT) ?: 0;
        $this->type = $device->getAttrib('ipmi_type');
    }

    public static function init(?Device $device = null): ?self
    {
        $device ??= DeviceCache::getPrimary();

        if ($device->getAttrib('ipmi_hostname') === null) {
            return null;
        }

        return new static($device);
    }

    /**
     * @param  string[]  $commands
     * @return string
     *
     * @throws IpmiConnectionFailed
     */
    public function command(array $commands): string
    {
        if ($this->type) {
            $result = $this->runCommand($commands);

            if ($result->failed()) {
                throw new IpmiConnectionFailed('Failed to connect to IPMI device');
            }

            return $result->output();
        }

        foreach (LibrenmsConfig::get('ipmi.type', []) as $ipmi_type) {
            try {
                Log::debug('Trying IPMI type: ' . $ipmi_type);
                $result = $this->runCommand($commands, $ipmi_type);

                if ($result->successful()) {
                    $this->device->setAttrib('ipmi_type', $ipmi_type);
                    $this->type = $ipmi_type;

                    return $result->output();
                }
            } catch (\Exception $e) {
                Log::error('IPMI Discovery error occurred: ' . $e->getMessage());
            }
        }

        throw new IpmiConnectionFailed('Failed to discover IPMI type');
    }

    /**
     * descr, value, unit, status, detail
     *
     * @return list<array{string, string, string, string, string}>
     *
     * @throws IpmiConnectionFailed
     */
    public function sdr(): array
    {
        $output = $this->command(['-c', 'sdr']);

        return array_map(
            fn (string $line): array => array_pad(array_values(array_map(trim(...), str_getcsv($line, escape: ''))), 5, null),
            array_filter(explode("\n", trim($output)))
        );
    }

    /**
     *  desc, current, unit, state, low_nonrecoverable, low_limit, low_warn, high_warn, high_limit, high_nonrecoverable
     *
     * @return list<array{string, string, string, string, string, string, string, string, string, string}>
     *
     * @throws IpmiConnectionFailed
     */
    public function sensors(): array
    {
        $output = $this->command(['sensor']);

        return array_map(
            fn (string $line): array => array_map(trim(...), explode('|', $line)),
            explode("\n", trim($output))
        );
    }

    /**
     * @param  string[]  $args
     * @param  string|null  $type
     * @return string[]
     */
    private function createCommand(array $args = [], ?string $type = null): array
    {
        $cmd = [$this->binary];

        if (! $this->isLocalhost()) {
            array_push($cmd, '-H', $this->hostname, '-U', $this->username, '-P', $this->password, '-L', 'USER');

            if ($this->port) {
                array_push($cmd, '-p', $this->port);
            }

            if ($this->kg_key) {
                array_push($cmd, '-y', $this->kg_key);
            }

            if ($this->ciphersuite) {
                array_push($cmd, '-C', $this->ciphersuite);
            }

            if ($this->timeout) {
                array_push($cmd, '-N', $this->timeout);
            }
        }

        $type ??= $this->type;
        if ($type) {
            array_push($cmd, '-I', $type);
        }

        array_push($cmd, ...$args);

        return $cmd;
    }

    private function isLocalhost(): bool
    {
        return in_array($this->hostname, [
            'localhost',
            '127.0.0.1',
            '::1',
            LibrenmsConfig::get('own_hostname'),
        ]);
    }

    /**
     * @param  string[]  $commands
     * @param  string|null  $ipmi_type
     * @return ProcessResult
     */
    private function runCommand(array $commands, ?string $ipmi_type = null): ProcessResult
    {
        $cmd = $this->createCommand($commands, $ipmi_type);
        Log::debug('IPMI[%m' . implode(' ', $cmd) . '%n]', ['color' => true]);

        return Process::command($cmd)->run();
    }
}
