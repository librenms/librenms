<?php
/*
 * Poller.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS;

use App\Models\Device;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use LibreNMS\Exceptions\PollerException;
use LibreNMS\Polling\ConnectivityHelper;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Dns;
use LibreNMS\Util\Git;

class Poller extends PollingCommon
{
    /** @var string */
    private $device_spec;
    /** @var array */
    private $module_override;
    private $output;
    private $polled = 0;
    private $unreachable = 0;


    /**
     * @var Device
     */
    private $device;
    /**
     * @var array
     */
    private $deviceArray;

    public function __construct(string $device_spec, array $module_override, \Illuminate\Console\OutputStyle $output)
    {
        $this->device_spec = $device_spec;
        $this->module_override = $module_override;
        $this->output = $output;
        $this->parseModules();
    }

    public function poll()
    {
        $this->printHeader();

        if (Debug::isEnabled()) {
            \LibreNMS\Util\OS::updateCache(true); // Force update of OS Cache
        }

        $this->output->writeln('Starting polling run:');
        $this->output->newLine();

        foreach ($this->buildDeviceQuery()->pluck('device_id') as $device_id) {
            \DeviceCache::setPrimary($device_id);
            $this->device = \DeviceCache::getPrimary();
            $this->device->ip = $this->device->overwrite_ip ?: Dns::lookupIp($this->device);

            $os_group = $this->initDeviceArray();
            $this->printDeviceInfo($os_group);
            $this->initRrdDirectory();

            $helper = new ConnectivityHelper($this->device);
            $helper->saveMetrics();

            if ($helper->isUp()) {
                $this->filterModules();
                dump('we are polling the device now (:');
            }
        }
    }

    private function moduleExists(string $module): bool{
        return is_file("includes/polling/$module.inc.php");
    }

    private function buildDeviceQuery(): Builder
    {
        $query = Device::query();

        if (empty($this->device_spec)) {
            throw new PollerException('Invalid device spec');
        } elseif ($this->device_spec == 'all') {
            return $query;
        } elseif ($this->device_spec == 'even') {
            return $query->where(DB::raw('device_id % 2'), 0);
        } elseif ($this->device_spec == 'odd') {
            return $query->where(DB::raw('device_id % 2'), 1);
        } elseif (is_numeric($this->device_spec)) {
            return $query->where('device_id', $this->device_spec);
        } elseif (Str::contains($this->device_spec, '*')) {
            return $query->where('hostname', 'like', str_replace('*', '%', $this->device_spec));
        }

        return $query->where('hostname', $this->device_spec);
    }

    private function initDeviceArray(): string
    {
        $this->deviceArray = $this->device->toArray();
        if ($os_group = Config::get("os.{$this->device->os}.group")) {
            $this->deviceArray['os_group'] = $os_group;
        }

        return $os_group;
    }

    private function initRrdDirectory(): void
    {
        $host_rrd = \Rrd::name($this->device->hostname, '', '');
        if (Config::get('rrd.enable', true) && ! is_dir($host_rrd)) {
            mkdir($host_rrd);
            $this->output->writeln("Created directory : $host_rrd");
        }
    }

    private function parseModules()
    {
        foreach ($this->module_override as $index => $module) {
            // parse submodules (only supported by some modules)
            if (Str::contains($module, '/')) {
                [$module, $submodule] = explode('/', $module, 2);
                $existing_submodules = Config::get("poller_submodules.$module", []);
                $existing_submodules[] = $submodule;
                Config::set("poller_submodules.$module", $existing_submodules);
            }

            if (! $this->moduleExists($module)) {
                unset($this->module_override[$index]);
                continue;
            }

            Config::set("poller_modules.$module", 1);
        }
    }

    private function filterModules(): void
    {
        if ($this->device->snmp_disable) {
            // only non-snmp modules
            Config::set('poller_modules', array_intersect_key(Config::get('poller_modules'), [
                'availability' => true,
                'ipmi' => true,
                'unix-agent' => true,
            ]));
        } else {
            // we always want the core module to be included, prepend it
            Config::set('poller_modules', ['core' => true] + Config::get('poller_modules'));
        }
    }

    private function printDeviceInfo($group)
    {
        $this->output->writeln(sprintf(<<<EOH
Hostname:  %s %s
ID:        %s
OS:        %s
IP:        %s
EOH, $this->device->hostname, $group ? " ($group)" : '', $this->device->device_id, $this->device->os, $this->device->ip));
        $this->output->newLine();
    }

    private function printModules()
    {
        $modules = array_map(function ($module) {
            $submodules = Config::get("poller_submodules.$module");

            return $module . ($submodules ? '(' . implode(',', $submodules) . ')' : '');
        }, array_keys(Config::get("poller_modules", [])));

        d_echo("Override poller modules: " . implode(', ', $modules) . PHP_EOL);
    }

    private function printHeader()
    {
        if (Debug::isEnabled() || Debug::isVerbose()) {
            $version = \LibreNMS\Util\Version::get();
            $this->output->writeln(sprintf(<<<EOH
===================================
Version info:
Commit SHA: %s
Commit Date: %s
DB Schema: %s
PHP: %s
MySQL: %s
RRDTool: %s
SNMP: %s
==================================
EOH,
                Git::localCommit(),
                Git::localDate(),
                vsprintf('%s (%s)', $version->database()),
                phpversion(),
                \LibreNMS\DB\Eloquent::isConnected() ? \LibreNMS\DB\Eloquent::version() : '?',
                $version->rrdtool(),
                $version->netSnmp()
            ));

        }
    }
}
