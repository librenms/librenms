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
use LibreNMS\Polling\Method\IpmiPollingMethod;

class Ipmitool
{
    private readonly string $binary;
    private ?string $type;

    public function __construct(
        private readonly Device $device,
        private readonly IpmiPollingMethod $method,
    ) {
        $this->binary = LibrenmsConfig::get('ipmitool', 'ipmitool');
        $this->type = $this->method->type;
    }

    public static function init(?Device $device = null): ?self
    {
        $device ??= DeviceCache::getPrimary();
        $ipmi = $device->getPollingMethods()->ipmi();

        if (! $ipmi->enabled || ! $ipmi->hostname) {
            return null;
        }

        return new static($device, $ipmi);
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
            array_push($cmd, '-H', $this->method->hostname, '-U', $this->method->username, '-P', $this->method->password, '-L', 'USER');

            if ($this->method->port) {
                array_push($cmd, '-p', $this->method->port);
            }

            if ($this->method->kgKey) {
                array_push($cmd, '-y', $this->method->kgKey);
            }

            if ($this->method->cipherSuite) {
                array_push($cmd, '-C', $this->method->cipherSuite);
            }

            if ($this->method->timeout) {
                array_push($cmd, '-N', $this->method->timeout);
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
        return in_array($this->method->hostname, [
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
