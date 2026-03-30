<?php

/*
 * NetSnmpTranslate.php
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
 * @copyright  2026 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.au>
 */

namespace LibreNMS\Data\Source;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Eventlog;
use App\Polling\Measure\Measurement;
use DeviceCache;
use Illuminate\Support\Str;
use LibreNMS\Enum\Severity;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Oid;
use Log;
use Symfony\Component\Process\Process;

class NetSnmpTranslate extends NetSnmpCmd
{
    protected array $options = [];
    protected array $mibDirs = [];
    protected Device $device;
    // defaults for net-snmp https://net-snmp.sourceforge.io/docs/man/snmpcmd.html
    protected array $mibs = ['SNMPv2-TC', 'SNMPv2-MIB', 'IF-MIB', 'IP-MIB', 'TCP-MIB', 'UDP-MIB', 'NET-SNMP-VACM-MIB'];

    public function __construct()
    {
        $this->device = DeviceCache::getPrimary();
    }

    /**
     * Easy way to start a new instance
     */
    public static function make(): NetSnmpTranslate
    {
        return new static;
    }

    /**
     * Specify a device to make the snmp query against.
     * By default the query will use the primary device.
     */
    public function device(Device $device): NetSnmpTranslate
    {
        $this->device = $device;

        return $this;
    }

    /**
     * Set an additional MIB directory to search for MIBs.
     * You do not need to specify the base and os directories, they are already included.
     */
    public function mibDir(?string $dir): NetSnmpTranslate
    {
        $this->mibDirs[] = $dir;

        return $this;
    }

    /**
     * Set MIBs to use for this query. Base mibs are included by default.
     * They will be appended to existing mibs unless $append is set to false.
     */
    public function mibs(array $mibs, bool $append = true): NetSnmpTranslate
    {
        $this->mibs = $append ? array_merge($this->mibs, $mibs) : $mibs;

        return $this;
    }

    /**
     * Output all OIDs numerically
     */
    public function numeric(bool $numeric = true): NetSnmpTranslate
    {
        $this->options = $numeric
            ? array_merge($this->options, ['-On'])
            : array_diff($this->options, ['-On']);

        return $this;
    }

    /**
     * Hide MIB in output
     */
    public function hideMib(): NetSnmpTranslate
    {
        $this->options = array_merge($this->options, ['-Os']);

        return $this;
    }

    /**
     * Translate an OID.
     * call numeric() on the query to output numeric OID
     */
    public function translate(string $oid): string
    {
        $oid = new Oid($oid);

        // user did not specify numeric, output full text
        if (! in_array('-On', $this->options)) {
            if (! in_array('-Os', $this->options)) {
                $this->options[] = '-OS'; // show full oid, unless hideMib is set
            }
        } elseif ($oid->isNumeric()) {
            return Str::start($oid, '.'); // numeric to numeric optimization
        }

        // if mib is not directly specified and it doesn't have a numeric root
        if (! $oid->hasMib() && ! $oid->hasNumericRoot()) {
            $this->options[] = '-IR'; // search for mib
        }

        return $this->exec('snmptranslate', [$oid])->value();
    }

    protected function buildCli(string $command, array $oids): array
    {
        $cmd = [LibrenmsConfig::get($command, $command)];

        array_push($cmd, '-M', $this->mibDirectories());
        array_push($cmd, '-m', implode(':', $this->mibs));

        return array_merge($cmd, $this->options, $oids);
    }
}
