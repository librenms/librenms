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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class Ipmitool
{
    private string $binary;
    private string $hostname;
    private int $port;
    private string $username;
    private string $password;
    private ?string $kg_key;
    private ?int $ciphersuite;
    private int $timeout;
    private ?string $type;

    public function __construct(
        private Device $device,
    )
    {
        $this->binary = LibrenmsConfig::get('ipmitool', 'ipmitool');
        $this->hostname = $device->getAttrib('ipmi_hostname', $device->hostname);
        $this->port = filter_var($device->getAttrib('ipmi_port'), FILTER_VALIDATE_INT) ?: 0;
        $this->username = $device->getAttrib('ipmi_username', '') ;
        $this->password = $device->getAttrib('ipmi_password', '');
        $this->kg_key = $device->getAttrib('ipmi_kg_key');
        $this->ciphersuite = $device->getAttrib('ipmi_ciphersuite');
        $this->timeout = filter_var($device->getAttrib('ipmi_timeout'), FILTER_VALIDATE_INT);
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
     */
    public function command(array $commands): string
    {
        // TODO add command logging of format IPMI[<cli command>]
        return Process::command($this->createCommand($commands))->run()->output();
    }

    public function sdr(): array
    {
        $output = $this->command(['-c', 'sdr']);

        return array_map(
            fn(string $line): array => array_values(array_map(trim(...), str_getcsv($line, escape: ''))),
            array_filter(explode("\n", trim($output)))
        );
    }

    public function sensors(): array
    {
        $output = $this->command(['sensor']);

        return array_map(
            fn(string $line): array => array_map(trim(...), explode('|', $line)),
            explode("\n", trim($output))
        );
    }

    private function createCommand(array $args = []): array
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

        array_push($cmd, '-I', $this->getInterfaceType($cmd), ...$args);

        return $cmd;
    }

    private function getInterfaceType(array $command): string
    {
        // fixme, run the intended command and return output, will take some restructuring

        if ($this->type) {
            return $this->type;
        }

        foreach (LibrenmsConfig::get('ipmi.type', []) as $ipmi_type) {
            // Check if the IPMI type is available, catch segfaults of ipmitool/freeipmi.
            try {
                Log::debug('Trying IPMI type: ' . $ipmi_type);
                $result = Process::command([...$command, '-I', $ipmi_type, 'chassis', 'power', 'status'])->run();

                if ($result->successful()) {
                    $this->device->setAttrib('ipmi_type', $ipmi_type);
                    $this->type = $ipmi_type;

                    return $ipmi_type;
                }
            } catch (\Exception $e) {
                Log::error('IPMI Discovery error occurred: ' . $e->getMessage());
            }
        }

        throw new \Exception('No Connection');
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
}
