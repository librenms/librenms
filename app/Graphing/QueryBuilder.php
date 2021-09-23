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

abstract class QueryBuilder
{
    protected $datastore;

    protected $fields = [];
    protected $currentField = -1;
    protected $start;
    protected $end;
    protected $minMax = false;

    /**
     * @var \App\Data\DataGroup
     */
    protected $dataGroup;

    public static function fromDataGroup(DataGroup $dataGroup): QueryBuilder
    {
        $new = app('graph-query-builder');
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

    public function getStart(): CarbonImmutable
    {
        return $this->start;
    }

    public function getEnd(): CarbonImmutable
    {
        return $this->end;
    }

    abstract public function toQuery();

    public function getDatastore()
    {
        return $this->datastore;
    }
}
