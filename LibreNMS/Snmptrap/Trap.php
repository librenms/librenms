<?php
/**
 * Trap.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Snmptrap;

use App\Models\Device;
use App\Models\Eventlog;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LibreNMS\Enum\Severity;
use LibreNMS\Util\IP;

class Trap
{
    public readonly string $raw;
    public readonly string $hostname;
    public readonly ?string $ip;
    protected Collection $oid_data;
    protected ?Device $device = null;

    /**
     * Construct a trap from raw trap text
     */
    public function __construct(string $trap)
    {
        $this->raw = $trap;

        $lines = explode(PHP_EOL, trim($trap));

        $this->hostname = array_shift($lines);

        $line = array_shift($lines);
        if ($line) {
            preg_match('/\[([0-9.:a-fA-F]+)]/', $line, $matches);
        }
        $this->ip = $matches[1] ?? '';

        // parse the oid data
        $this->oid_data = (new Collection($lines))->mapWithKeys(function ($line) {
            [$oid, $data] = explode(' ', $line, 2);

            return [$oid => trim($data, '"')];
        });
    }

    /**
     * Find the first in this trap by substring
     *
     * @param  string|string[]  $search
     * @return string
     */
    public function findOid(array|string $search): string
    {
        return $this->oid_data->keys()->first(function ($oid) use ($search) {
            return Str::contains($oid, $search);
        }, '');
    }

    /**
     * Find all oids that match the given string
     *
     * @param  string|string[]  $search
     * @return array
     */
    public function findOids(array|string $search): array
    {
        return $this->oid_data->keys()->filter(function ($oid) use ($search) {
            return Str::contains($oid, $search);
        })->all();
    }

    public function getOidData(string $oid): string
    {
        return $this->oid_data->get($oid, '');
    }

    public function getDevice(): ?Device
    {
        if (is_null($this->device) && IP::isValid($this->ip)) {
            $this->device = Device::findByHostname($this->hostname) ?: Device::findByIp($this->ip);
        }

        return $this->device;
    }

    public function getTrapOid(): string
    {
        return $this->getOidData('SNMPv2-MIB::snmpTrapOID.0');
    }

    /**
     * Render the Trap for debugging purpose
     *
     * @param  bool  $detailed
     * @return string
     */
    public function toString(bool $detailed = false): string
    {
        if ($detailed) {
            return $this->getTrapOid() . "\n" . json_encode($this->oid_data->reject(function ($value, $key) {
                return Str::contains($key, 'SNMPv2-MIB::snmpTrapOID.0');
            })->all());
        }

        return $this->getTrapOid();
    }

    /**
     * Log this trap in the eventlog with the given message
     */
    public function log(string $message, Severity $severity = Severity::Info, string $type = 'trap', int|null|string $reference = null): void
    {
        Eventlog::log($message, $this->getDevice(), $type, $severity, $reference);
    }
}
