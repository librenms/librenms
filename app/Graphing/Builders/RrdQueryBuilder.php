<?php
/*
 * RrdQueryBuilder.php
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

use App\Graphing\QueryBuilder;
use Rrd;

class RrdQueryBuilder extends QueryBuilder
{
    protected $datastore = 'rrd';

    public function toQuery()
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
}
