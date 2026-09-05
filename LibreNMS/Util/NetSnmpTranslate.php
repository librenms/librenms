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

namespace LibreNMS\Util;

use App\Events\SnmpQueryExecuted;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Polling\Measure\Measurement;
use DeviceCache;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LibreNMS\Data\Source\SnmpResponse;
use Symfony\Component\Process\Process;

class NetSnmpTranslate
{
    /** @var string[] */
    private array $options = [];
    /** @var string[] */
    private array $mibDirs = [];
    private Device $device;
    // defaults for net-snmp https://net-snmp.sourceforge.io/docs/man/snmpcmd.html
    /** @var string[] */
    private array $mibs = ['SNMPv2-TC', 'SNMPv2-MIB', 'IF-MIB', 'IP-MIB', 'TCP-MIB', 'UDP-MIB', 'NET-SNMP-VACM-MIB'];

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
     *
     * @param  string[]  $mibs
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
     * Set option(s) for net-snmp command line. Overrides the default options.
     * Some options may break parsing, but you can manually parse the raw output if needed.
     * This will override other options set such as setting numeric.
     * Calling with null will reset to the default options (-OQXUte).
     * Try to avoid setting options this way to keep the API generic.
     *
     * @param  string[]|string|null  $options
     * @return $this
     */
    public function options($options = []): NetSnmpTranslate
    {
        $this->options = $options !== null
            ? Arr::wrap($options)
            : [];

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

    /**
     * @param  string[]  $oids
     * @return string[]
     */
    private function buildCli(string $command, array $oids): array
    {
        $cmd = [LibrenmsConfig::get($command, $command)];

        array_push($cmd, '-M', $this->mibDirectories());
        array_push($cmd, '-m', implode(':', $this->mibs));

        return array_merge($cmd, $this->options, $oids);
    }

    /**
     * @param  string[]  $oids
     */
    private function exec(string $command, array $oids): SnmpResponse
    {
        $measure = Measurement::start($command);
        $cliCommand = $this->buildCli($command, $oids);
        $proc = new Process($cliCommand);
        $proc->setTimeout(LibrenmsConfig::get('snmp.exec_timeout', 1200));

        $proc->run();

        $response = new SnmpResponse(
            $proc->getOutput(),
            $proc->getErrorOutput(),
            $proc->getExitCode(),
        );

        event(new SnmpQueryExecuted(
            method: $command,
            oids: $oids,
            cliCommand: $cliCommand,
            response: $response,
            device: $this->device,
            mibs: $this->mibs,
            mibDir: implode(':', $this->mibDirs),
        ));

        $measure->manager()->recordSnmp($measure->end());

        return $response;
    }

    private function mibDirectories(): string
    {
        $base = LibrenmsConfig::get('mib_dir');
        $dirs = [$base];

        // os group
        if ($os_group = LibrenmsConfig::getOsSetting($this->device->os, 'group')) {
            if (file_exists("$base/$os_group")) {
                $dirs[] = "$base/$os_group";
            }
        }

        // os directory
        $os_mibdir = LibrenmsConfig::getOsSetting($this->device->os, 'mib_dir');
        if ($os_mibdir && is_string($os_mibdir)) {
            $dirs[] = "$base/$os_mibdir";
        } elseif (file_exists($base . '/' . $this->device->os)) {
            $dirs[] = $base . '/' . $this->device->os;
        }

        foreach ($this->mibDirs as $mibDir) {
            $dirs[] = "$base/$mibDir";
        }

        // remove trailing /, remove empty dirs, and remove duplicates
        $dirs = array_unique(array_filter(array_map(fn ($dir) => rtrim((string) $dir, '/'), $dirs)));

        return implode(':', $dirs);
    }
}
