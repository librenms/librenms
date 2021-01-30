<?php
/*
 * QueryBuilder.php
 *
 * Build a query that can be generically executed across various time series databases.
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

namespace App\Graphing;

use App\Data\DataGroup;
use Carbon\CarbonImmutable;
use Rrd;

class QueryBuilder
{
    private $fields = [];
    private $currentField = -1;
    private $start;
    private $end;
    private $minMax = false;

    /**
     * @var \App\Data\DataGroup
     */
    private $dataGroup;

    public static function fromDataGroup(DataGroup $dataGroup)
    {
        $new = new static();
        $new->dataGroup = $dataGroup;
        return $new;
    }

    public function select($field): QueryBuilder
    {
        $this->fields[++$this->currentField] = ['name' => $field];
        return $this;
    }

    public function math($operator, $value): QueryBuilder
    {
        $this->fields[$this->currentField]['op'] = $operator;
        $this->fields[$this->currentField]['value'] = $value;
        return $this;
    }

    public function range(CarbonImmutable $from, CarbonImmutable $to)
    {
        $this->start = $from;
        $this->end = $to;
        return $this;
    }

    public function enableMinMax()
    {
        $this->minMax = true;
    }

    public function toInfluxDBQuery()
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

    public function toRrdQuery()
    {
        $defs = [];

        foreach ($this->fields as $index => $field) {
            $name = $field['name'];
            $ds = $this->dataGroup->getDataSet($name);
            $rrd_file = Rrd::fileName($this->dataGroup, $ds);
            $defs[] = "DEF:{$name}_raw_$index=$rrd_file:value:AVERAGE";
            if (isset($field['op'], $field['value'])) {
                $defs[] = "CDEF:{$name}_calc_$index={$name}_raw_$index,{$field['value']},{$field['op']}";
                $defs[] = "XPORT:{$name}_calc_$index:'$name'";
            } else {
                $defs[] = "XPORT:{$name}_raw_$index:'$name'";
            }
        }

        return $defs;
    }

    public function getStart(): CarbonImmutable
    {
        return $this->start;
    }

    public function getEnd(): CarbonImmutable
    {
        return $this->end;
    }
}
