<?php
/*
 * Load.php
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

use App\Facades\Rrd;
use LibreNMS\Services\DefaultServiceCheck;

class Load extends DefaultServiceCheck
{
    public function serviceDataSets(): array
    {
        return ['load' => ''];
    }

    public function graphRrdCommands(string $ds): string
    {
        $rrd_filename = $this->rrdName('load1');
        if ($ds == 'load' && Rrd::checkRrdExists($rrd_filename)) {
            $graph = ' DEF:DS0=' . $rrd_filename . ':value:AVERAGE ';
            $graph .= ' LINE1.25:DS0#' . \LibreNMS\Config::get('graph_colours.mixed.0') . ":'" . str_pad(substr('Load 1', 0, 15), 15) . "' ";
            $graph .= ' GPRINT:DS0:LAST:%5.2lf%s ';
            $graph .= ' GPRINT:DS0:AVERAGE:%5.2lf%s ';
            $graph .= ' GPRINT:DS0:MAX:%5.2lf%s\\l ';

            $rrd_filename = $this->rrdName('load5');
            $graph .= ' DEF:DS1=' . $rrd_filename . ':value:AVERAGE ';
            $graph .= ' LINE1.25:DS1#' . \LibreNMS\Config::get('graph_colours.mixed.1') . ":'" . str_pad(substr('Load 5', 0, 15), 15) . "' ";
            $graph .= ' GPRINT:DS1:LAST:%5.2lf%s ';
            $graph .= ' GPRINT:DS1:AVERAGE:%5.2lf%s ';
            $graph .= ' GPRINT:DS1:MAX:%5.2lf%s\\l ';

            $rrd_filename = $this->rrdName('load15');
            $graph .= ' DEF:DS2=' . $rrd_filename . ':value:AVERAGE ';
            $graph .= ' LINE1.25:DS2#' . \LibreNMS\Config::get('graph_colours.mixed.2') . ":'" . str_pad(substr('Load 15', 0, 15), 15) . "' ";
            $graph .= ' GPRINT:DS2:LAST:%5.2lf%s ';
            $graph .= ' GPRINT:DS2:AVERAGE:%5.2lf%s ';
            $graph .= ' GPRINT:DS2:MAX:%5.2lf%s\\l ';

            return $graph;
        }

        return parent::graphRrdCommands($ds);
    }
}
