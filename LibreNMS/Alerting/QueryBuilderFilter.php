<?php
/**
 * QueryBuilderFilter.php
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

namespace LibreNMS\Alerting;

use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\DB\Schema;

class QueryBuilderFilter implements \JsonSerializable
{
    private static $table_blacklist = [
        'device_group_device',
        'alerts',
        'alert_log',
    ];

    private $filter = [];
    private $schema;

    /**
     * QueryBuilderFilter constructor.
     * @param string $type alert|group
     */
    public function __construct($type = 'alert')
    {
        $this->schema = new Schema();

        if ($type == 'alert') {
            $this->generateMacroFilter('alert.macros.rule');
        } elseif ($type == 'group') {
            $this->generateMacroFilter('alert.macros.group');
        }

        $this->generateTableFilter();
    }

    private function generateMacroFilter($config_location)
    {
        $macros = Config::get($config_location, []);
        krsort($macros);

        foreach ($macros as $key => $value) {
            $field = 'macros.' . $key;

            if (preg_match('/^past_\d+m$/', $key)) {
                continue; // don't include the time based macros, they don't work like that
            }

            if ((Str::endsWith($key, '_usage_perc')) || (Str::startsWith($key, 'packet_loss_'))) {
                $this->filter[$field] = [
                    'id' => $field,
                    'type' => 'integer',
                ];
            } else {
                $this->filter[$field] = [
                    'id' => $field,
                    'type' => 'integer',
                    'input' => 'radio',
                    'values' => ['1' => 'Yes', '0' => 'No'],
                    'operators' => ['equal'],
                ];
            }
        }
    }

    private function generateTableFilter()
    {
        $db_schema = $this->schema->getSchema();
        $valid_tables = array_diff(array_keys($this->schema->getAllRelationshipPaths()), self::$table_blacklist);

        foreach ((array) $db_schema as $table => $data) {
            $columns = array_column($data['Columns'], 'Type', 'Field');

            // only allow tables with a direct association to device_id
            if (! in_array($table, $valid_tables)) {
                continue;
            }

            foreach ($columns as $column => $column_type) {
                // ignore device id columns, except in the devices table
                if ($column == 'device_id' && $table != 'devices') {
                    continue;
                }

                $type = $this->getColumnType($column_type);

                // ignore unsupported types (such as binary and blob)
                if (is_null($type)) {
                    continue;
                }

                $field = "$table.$column";

                if (Str::endsWith($column, ['_perc', '_current', '_usage', '_perc_warn'])) {
                    $this->filter[$field] = [
                        'id' => $field,
                        'type' => 'string',
                    ];
                } elseif ($type == 'enum') {// format enums as radios
                    $values = explode(',', substr($column_type, 4));
                    $values = array_map(function ($val) {
                        return trim($val, "()' ");
                    }, $values);

                    $this->filter[$field] = [
                        'id' => $field,
                        'type' => 'integer',
                        'input' => 'radio',
                        'values' => $values,
                        'operators' => ['equal'],
                    ];
                } else {
                    $this->filter[$field] = [
                        'id' => $field,
                        'type' => $type,
                    ];
                }
            }
        }
    }

    private function getColumnType($type)
    {
        if (Str::startsWith($type, ['varchar', 'text', 'double', 'float'])) {
            return 'string';
        } elseif (Str::startsWith($type, ['int', 'tinyint', 'smallint', 'mediumint', 'bigint'])) {
            //TODO implement field selection and change back to integer
            return 'string';
        } elseif (Str::startsWith($type, ['timestamp', 'datetime'])) {
            return 'datetime';
        } elseif (Str::startsWith($type, 'enum')) {
            return 'enum';
        }

        // binary, blob
        return null;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $filter = $this->filter;
        asort($filter);

        return array_values($filter);
    }

    /**
     * Get the filter for a specific item
     *
     * @param string $id
     * @return array|null
     */
    public function getFilter($id)
    {
        if (array_key_exists($id, $this->filter)) {
            return $this->filter[$id];
        }

        return null;
    }
}
