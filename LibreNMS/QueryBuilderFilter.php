<?php
/**
 * QueryBuilderFilter.php
 *
 * Creates filter for jQuery Query Builder
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

namespace LibreNMS;

use Cache;
use DB;
use JsonSerializable;
use LibreNMS\Config;
use Settings;
use Symfony\Component\Yaml\Yaml;

class QueryBuilderFilter implements JsonSerializable
{
    private $filter = [];

    /**
     * @var array List of tables we can resolve glue from (not directly containing device_id)
     */
    private static $table_whitelist = ['state_translations', 'application_metrics'];

    /**
     * QueryBuilderFilter constructor.
     * @param string $type alert|group
     */
    public function __construct($type = 'alert')
    {
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
            if (ends_with($key, '_usage_perc')) {
                $this->filter[] = [
                    'id' => 'macros.' . $key,
                    'type' => 'integer',
                ];
            } else {
                $this->filter[] = [
                    'id' => 'macros.' . $key,
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
        $db_schema = Yaml::parse(file_get_contents(Config::get('install_dir') . '/misc/db_schema.yaml'));

        foreach ((array)$db_schema as $table => $data) {
            $columns = array_column($data['Columns'], 'Type', 'Field');

            // only allow tables with a direct association to device_id
            if (!isset($columns['device_id']) && !in_array($table, self::$table_whitelist)) {
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

                // format enums as radios
                if ($type == 'enum') {
                    $values = explode(',', substr($column_type, 4));
                    $values = array_map(function ($val) {
                        return trim($val, "()' ");
                    }, $values);

                    $this->filter[] = [
                        'id' => "$table.$column",
                        'type' => 'integer',
                        'input' => 'radio',
                        'values' => $values,
                        'operators' => ['equal'],
                    ];
                } else {
                    $this->filter[] = [
                        'id' => "$table.$column",
                        'type' => $type,
                    ];
                }
            }
        }
    }


    private function getColumnType($type)
    {
        if (starts_with($type, 'varchar')) {
            return 'string';
        } elseif (starts_with($type, ['int', 'tinyint', 'smallint', 'mediumint', 'bigint'])) {
            return 'integer';
        } elseif (starts_with($type, 'double')) {
            return 'double';
        } elseif ($type == 'timestamp') {
            return 'datetime';
        } elseif (starts_with($type, 'enum')) {
            return 'enum';
        }

        // binary, blob
        return null;
    }


    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->filter;
    }
}
