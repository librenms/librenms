<?php
/**
 * table_columns.inc.php
 *
 * List Table Columns
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
 * @copyright  2018 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

use LibreNMS\Alerting\QueryBuilderFilter;
use LibreNMS\DB\Schema;

$query = '';
$where = [];
$params = [];

$schema = new Schema();

$columns = [];
foreach ($schema->getSchema() as $table => $data) {
    $tmp_columns = $schema->getColumns($table);
    $new_columns = array_filter(
        array_map(function ($column) use ($table, $vars) {
            if (empty($vars['search']) || str_contains("$table.$column", $vars['search'])) {
                return [
                    'id' => "$table.$column",
                    'text' => "$table.$column",
                ];
            }
        }, $tmp_columns)
    );
    $columns = array_merge($columns, $new_columns);
}


return [$columns];
