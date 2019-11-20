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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Snmptrap;

use App\Models\Device;
use Illuminate\Support\Collection;
use LibreNMS\Snmptrap\Handlers\Fallback;
use LibreNMS\Util\IP;
use Log;

class Trap
{
    protected $raw;
    protected $hostname;
    protected $ip;

    protected $device;

    /** @var Collection $oid_data */
    protected $oid_data;

    /**
     * Construct a trap from raw trap text
     * @param $trap
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
        };

        // parse the oid data
        $this->oid_data = collect($lines)->mapWithKeys(function ($line) {
            list($oid, $data) = explode(' ', $line, 2);
            return [$oid => trim($data, '"')];
        });
    }

    /**
     * Find the first in this trap by substring
     *
     * @param $search
     * @return string
     */
    public function findOid($search)
    {
        return $this->oid_data->keys()->first(function ($oid) use ($search) {
            return str_contains($oid, $search);
        });
    }

    /**
     * Find all oids that match the given string
     * @param $search
     * @return array
     */
    public function findOids($search)
    {
        return $this->oid_data->keys()->filter(function ($oid) use ($search) {
            return str_contains($oid, $search);
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
}
