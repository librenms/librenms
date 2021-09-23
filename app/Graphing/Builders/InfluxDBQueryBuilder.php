<?php
/*
 * InfluxDBQueryBuilder.php
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

namespace App\Graphing\Builders;

class InfluxDBQueryBuilder extends \App\Graphing\QueryBuilder
{
    protected $datastore = 'influxdb';

    public function toQuery()
    {
        $fields = [];
        foreach ($this->fields as $field) {
            $select = "non_negative_derivative(mean(\"{$field['name']}\"), 1s)";
            if (isset($field['op'])) {
                $select .= ' ' . $field['op'] . $field['value'];
            }
            $fields[] = $select . " AS \"{$field['name']}\"";
        }
        $query = 'SELECT ' . implode(', ', $fields);

        $query .= ' FROM "' . $this->dataGroup->getName() . '"';

        $where = [];
        foreach ($this->dataGroup->getTags() as $tagK => $tagV) {
            $where[] = "$tagK = '$tagV'";
        }
        if ($this->start) {
            $where[] = 'time >= ' . $this->start->timestamp . 's';
        }
        if ($this->end) {
            $where[] = 'time <= ' . $this->end->timestamp . 's';
        }

        $query .= ' WHERE ' . implode(' AND ', $where);
        $query .= ' GROUP BY time(15s) fill(null)';

        return $query;
    }
}
