<?php
/*
 * InfluxDB2Builder.php
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

class InfluxDB2QueryBuilder extends \App\Graphing\QueryBuilder
{
    protected $datastore = 'influxdb2';

    public function toQuery()
    {
        $bucket = \LibreNMS\Config::get('influxdb2.bucket', 'librenms');
        $query = 'from(bucket: "' . $bucket . '")';
        $query .= ' |> range(start: ' . $this->start->timestamp . ', stop: ' . $this->end->timestamp . ')';
        $query .= ' |> filter(fn: (r) => r["_measurement"] == "' . $this->dataGroup->getName() . '"';

        foreach ($this->dataGroup->getTags() as $key => $value) {
            $query .= ' and r["' . $key . '"] == "' . $value . '"';
        }
        $query .= ')';

        $fields = [];
        $maps = [];
        $map = false;
        foreach ($this->fields as $field) {
            $fields[] = 'r["_field"] == "' . $field['name'] . '"';
            $mapQuery = $field['name'] . ': r.' . $field['name'];
            if (isset($field['op'])) {
                $mapQuery .= ' ' . $field['op'] . ' ' . $field['value'];
                $map = true;
            }
            $maps[] = $mapQuery;
        }
        $query .= ' |> filter(fn: (r) => ' . implode(' or ', $fields) . ')';
        $query .= ' |> derivative(nonNegative: true)';

        if ($map) {
            $query .= ' |> map(fn: (r) => ({ r with ' . implode(', ', $maps) . '}))';
        }

        $query .= ' |> aggregateWindow(every: 15s, fn: mean, createEmpty: false)';
        $query .= ' |> yield(name: "mean")';

        return $query;
    }
}
