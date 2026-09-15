<?php

/*
 * QueryBuilderFilterHook.php
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
 */

namespace App\Plugins\Hooks;

abstract class QueryBuilderFilterHook implements \LibreNMS\Interfaces\Plugins\Hooks\QueryBuilderFilterHook
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Filters to add to the alert rule and device group query builders, keyed by name.
     * The name is prefixed with the plugin name to build the field id (macros.pluginname_name).
     *
     * Each filter is a jQuery QueryBuilder filter definition (type, label, operators, ...)
     * plus a required 'sql' entry containing the sql to substitute for the field.
     * Reference the device being checked as %devices.device_id, for example:
     *
     * ['sql' => '(SELECT value FROM my_table WHERE my_table.device_id = %devices.device_id)', 'type' => 'string']
     *
     * @return array<string, array>
     */
    abstract public function filters(): array;

    final public function handle(string $pluginName): array
    {
        $filters = [];

        foreach ($this->filters() as $name => $filter) {
            $filters[$pluginName . '_' . $name] = $filter;
        }

        return $filters;
    }
}
