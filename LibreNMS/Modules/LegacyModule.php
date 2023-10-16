<?php
/**
 * LegacyModule.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use App\Models\Device;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use LibreNMS\Component;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\Util\Debug;
use Symfony\Component\Yaml\Yaml;

class LegacyModule implements Module
{
    /** @var array */
    private $module_deps = [
        'arp-table' => ['ports'],
        'bgp-peers' => ['ports', 'vrf'],
        'cisco-mac-accounting' => ['ports'],
        'fdb-table' => ['ports', 'vlans'],
        'vlans' => ['ports'],
        'vrf' => ['ports'],
    ];

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return $this->module_deps[$this->name] ?? [];
    }

    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        return $this->shouldPoll($os, $status);
    }

    public function discover(OS $os): void
    {
        if (! \LibreNMS\Util\Module::legacyDiscoveryExists($this->name)) {
            echo "Module $this->name does not exist, please remove it from your configuration";

            return;
        }

        $device = &$os->getDeviceArray();
        Debug::disableErrorReporting(); // ignore errors in legacy code

        include_once base_path('includes/datastore.inc.php');
        include_once base_path('includes/dbFacile.php');
        include_once base_path('includes/rewrites.php');
        include base_path("includes/discovery/$this->name.inc.php");

        Debug::enableErrorReporting(); // and back to normal
    }

    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        // all legacy modules require snmp except ipmi and unix-agent
        return $status->isEnabledAndDeviceUp($os->getDevice(), check_snmp: ! in_array($this->name, ['ipmi', 'unix-agent']));
    }

    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        if (! \LibreNMS\Util\Module::legacyPollingExists($this->name)) {
            echo "Module $this->name does not exist, please remove it from your configuration";

            return;
        }

        $device = &$os->getDeviceArray();
        Debug::disableErrorReporting(); // ignore errors in legacy code
        global $agent_data;

        include_once base_path('includes/datastore.inc.php');
        include_once base_path('includes/dbFacile.php');
        include_once base_path('includes/rewrites.php');
        include base_path("includes/polling/$this->name.inc.php");

        Debug::enableErrorReporting(); // and back to normal
    }

    public function cleanup(Device $device): void
    {
        // TODO: Implement cleanup() method.
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device)
    {
        $data = [];
        $dump_rules = $this->moduleDumpDefinition();
        if (empty($dump_rules)) {
            return false; // not supported for this legacy module
        }

        foreach ($dump_rules as $table => $info) {
            if ($table == 'component') {
                $components = $this->collectComponents($device->device_id);
                if (! empty($components)) {
                    $data[$table] = $components;
                }
                continue;
            }

            // check for custom where
            $where = $info['custom_where'] ?? "WHERE `$table`.`device_id`=?";
            $params = [$device->device_id];

            // build joins
            $join = '';
            $select = ["`$table`.*"];
            foreach ($info['joins'] ?? [] as $join_info) {
                if (isset($join_info['custom'])) {
                    $join .= ' ' . $join_info['custom'];

                    $default_select = [];
                } else {
                    [$left, $lkey] = explode('.', $join_info['left']);
                    [$right, $rkey] = explode('.', $join_info['right']);
                    $join .= " LEFT JOIN `$right` ON (`$left`.`$lkey` = `$right`.`$rkey`)";

                    $default_select = ["`$right`.*"];
                }

                // build selects
                $select = array_merge($select, isset($join_info['select']) ? (array) $join_info['select'] : $default_select);
            }

            $order_by = isset($info['order_by']) ? " ORDER BY {$info['order_by']}" : '';
            $fields = implode(', ', $select);
            $rows = DB::select("SELECT $fields FROM `$table` $join $where $order_by", $params);

            // don't include empty tables
            if (empty($rows)) {
                continue;
            }

            // remove unwanted fields
            if (isset($info['included_fields'])) {
                $keys = array_flip($info['included_fields']);
                $rows = array_map(function ($row) use ($keys) {
                    return array_intersect_key((array) $row, $keys);
                }, $rows);
            } elseif (isset($info['excluded_fields'])) {
                $keys = array_flip($info['excluded_fields']);
                $rows = array_map(function ($row) use ($keys) {
                    return array_diff_key((array) $row, $keys);
                }, $rows);
            }

            $data[$table] = $rows;
        }

        return $data;
    }

    private function moduleDumpDefinition(): array
    {
        static $def;

        if ($def === null) {
            // only load the yaml once, then keep it in memory
            $def = Yaml::parse(file_get_contents(base_path('/tests/module_tables.yaml')));
        }

        return $def[$this->name] ?? [];
    }

    private function collectComponents(int $device_id): array
    {
        $components = (new Component())->getComponents($device_id)[$device_id] ?? [];
        $components = Arr::sort($components, function ($item) {
            return $item['type'] . $item['label'];
        });

        return array_values($components);
    }
}
