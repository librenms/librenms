<?php
/*
 * Icmp.php
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Services\Checks;

use LibreNMS\Services\DefaultServiceCheck;

class Icmp extends DefaultServiceCheck
{
    public function serviceDataSets(): array
    {
        return [
            'rtt' => 's',
            'pl' => '%',
        ];
    }

    public function graphRrdCommands(string $rrd_filename, string $ds): string
    {
        $mixed = \LibreNMS\Config::get('graph_colours.mixed');
        if ($ds == 'rtt') {
            $graph = ' DEF:DS0=' . $rrd_filename . ':rta:AVERAGE ';
            $graph .= ' LINE1.25:DS0#' . $mixed[0] . ":'" . str_pad('Round Trip Avg', 15) . "' ";
            $graph .= ' GPRINT:DS0:LAST:\'%5.2lf%ss\' ';
            $graph .= ' GPRINT:DS0:AVERAGE:\'%5.2lf%ss\' ';
            $graph .= ' GPRINT:DS0:MAX:\'%5.2lf%ss\'\\l ';
            $graph .= ' DEF:DS1=' . $rrd_filename . ':rtmax:AVERAGE ';
            $graph .= ' LINE1.25:DS1#' . $mixed[1] . ":'" . str_pad('Round Trip Max', 15) . "' ";
            $graph .= ' GPRINT:DS1:LAST:\'%5.2lf%ss\' ';
            $graph .= ' GPRINT:DS1:AVERAGE:\'%5.2lf%ss\' ';
            $graph .= ' GPRINT:DS1:MAX:\'%5.2lf%ss\'\\l ';
            $graph .= ' DEF:DS2=' . $rrd_filename . ':rtmin:AVERAGE ';
            $graph .= ' LINE1.25:DS2#' . $mixed[2] . ":'" . str_pad('Round Trip Min', 15) . "' ";
            $graph .= ' GPRINT:DS2:LAST:\'%5.2lf%ss\' ';
            $graph .= ' GPRINT:DS2:AVERAGE:\'%5.2lf%ss\' ';
            $graph .= ' GPRINT:DS2:MAX:\'%5.2lf%ss\'\\l ';

            return $graph;
        } elseif ($ds == 'pl') {
            $graph = ' DEF:DS0=' . $rrd_filename . ':pl:AVERAGE ';
            $graph .= ' AREA:DS0#' . $mixed[2] . ":'" . str_pad('Packet Loss (%)', 16) . "' ";
            $graph .= ' GPRINT:DS0:LAST:\'%5.2lf%%\' ';
            $graph .= ' GPRINT:DS0:AVERAGE:\'%5.2lf%%\' ';
            $graph .= ' GPRINT:DS0:MAX:\'%5.2lf%%\'\\l ';

            return $graph;
        }

        return parent::graphRrdCommands($rrd_filename, $ds);
    }
}
