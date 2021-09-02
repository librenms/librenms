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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Snmptrap;

use App\Models\Device;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LibreNMS\Util\IP;

class Trap
{
    protected $raw;
    protected $hostname;
    protected $ip;

    protected $device;

    /** @var Collection */
    protected $oid_data;

    /**
     * Construct a trap from raw trap text
     * @param string $trap
     */
    public function __construct($trap)
    {
        $this->raw = $trap;
        $this->parse();
    }

    protected function parse()
    {
        $lines = explode(PHP_EOL, trim($this->raw));

        $this->hostname = array_shift($lines);

        $line = array_shift($lines);
        if (preg_match('/\[([0-9.:a-fA-F]+)\]/', $line, $matches)) {
            $this->ip = $matches[1];
        }

        // parse the oid data
        $this->oid_data = collect($lines)->mapWithKeys(function ($line) {
            [$oid, $data] = explode(' ', $line, 2);

            return [$oid => trim($data, '"')];
        });
    }

    /**
     * Find the first in this trap by substring
     *
     * @param string|string[] $search
     * @return string
     */
    public function findOid($search)
    {
        return $this->oid_data->keys()->first(function ($oid) use ($search) {
            return Str::contains($oid, $search);
        });
    }

    /**
     * Find all oids that match the given string
     * @param string|string[] $search
     * @return array
     */
    public function findOids($search)
    {
        return $this->oid_data->keys()->filter(function ($oid) use ($search) {
            return Str::contains($oid, $search);
        })->all();
    }

    public function getOidData($oid)
    {
        return $this->oid_data->get($oid);
    }

    /**
     * @return Device|null
     */
    public function getDevice()
    {
        if (is_null($this->device) && IP::isValid($this->ip)) {
            $this->device = Device::findByHostname($this->hostname) ?: Device::findByIp($this->ip);
        }

        return $this->device;
    }

    /**
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @return string
     */
    public function getTrapOid()
    {
        return $this->getOidData('SNMPv2-MIB::snmpTrapOID.0');
    }

    /**
     * @return string
     */
    public function getRaw()
    {
        return $this->raw;
    }

    /**
     * Render the Trap for debugging purpose
     *
     * @param bool $detailed
     * @return string
     */
    public function toString($detailed = false)
    {
        if ($detailed) {
            return $this->getTrapOid() . "\n" . json_encode($this->oid_data->reject(function ($value, $key) {
                return Str::contains($key, 'SNMPv2-MIB::snmpTrapOID.0');
            })->all());
        }

        return '' . $this->getTrapOid();
    }
}
