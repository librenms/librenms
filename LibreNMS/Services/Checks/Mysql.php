<?php
/*
 * Mysql.php
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

class Mysql extends DefaultServiceCheck
{
    public function hasDefaults(): array
    {
        return array_merge(parent::hasDefaults(), [
            '-d' => trans('service.check_params.mysql.-d.description'),
        ]);
    }

    public function getDefault(string $flag): string
    {
        if ($flag == '-d') {
            return 'mysql';
        }

        return parent::getDefault($flag);
    }

    public function serviceDataSets(): array
    {
        return [
            'mysqlqueries' => 'c',
            'mysql' => 'c',
            'mysqluptime' => 'c',
            'mysqlQcache' => 'c',
        ];
    }

    public function graphRrdCommands(string $ds): string
    {
        $mixed_colours = \LibreNMS\Config::get('graph_colours.mixed');
        $graph = '';

        if ($ds == 'mysqlqueries') {
            $rrd_filename = $this->rrdName('Queries');
            if (Rrd::checkRrdExists($rrd_filename)) {
                $graph .= ' DEF:DS0=' . $rrd_filename . ':value:AVERAGE ';
                $graph .= ' LINE1.25:DS0#' . $mixed_colours[1] . ":'" . str_pad(substr('Queries', 0, 19), 19) . "' ";
                $graph .= ' GPRINT:DS0:LAST:%0.0lf ';
                $graph .= ' GPRINT:DS0:AVERAGE:%0.0lf ';
                $graph .= ' GPRINT:DS0:MAX:%0.0lf\\l ';

                $rrd_filename = $this->rrdName('Questions');
                $graph .= ' DEF:DS1=' . $rrd_filename . ':value:AVERAGE ';
                $graph .= ' LINE1.25:DS1#' . $mixed_colours[2] . ":'" . str_pad(substr('Questions', 0, 19), 19) . "' ";
                $graph .= ' GPRINT:DS1:LAST:%0.0lf ';
                $graph .= ' GPRINT:DS1:AVERAGE:%0.0lf ';
                $graph .= ' GPRINT:DS1:MAX:%0.0lf\\l ';

                return $graph;
            }
        } elseif ($ds == 'mysql') {
            $rrd_filename = $this->rrdName('Connections');
            if (Rrd::checkRrdExists($rrd_filename)) {
                $graph .= ' DEF:DS0=' . $rrd_filename . ':value:AVERAGE ';
                $graph .= ' LINE1.25:DS0#' . $mixed_colours[0] . ":'" . str_pad(substr('Connections', 0, 19), 19) . "' ";
                $graph .= ' GPRINT:DS0:LAST:%5.2lf%s ';
                $graph .= ' GPRINT:DS0:AVERAGE:%5.2lf%s ';
                $graph .= ' GPRINT:DS0:MAX:%5.2lf%s\\l ';

                $rrd_filename = $this->rrdName('Open_files');
                if (Rrd::checkRrdExists($rrd_filename)) {
                    $graph .= ' DEF:DS1=' . $rrd_filename . ':value:AVERAGE ';
                    $graph .= ' LINE1.25:DS1#' . $mixed_colours[3] . ":'" . str_pad(substr('Open_files', 0, 19), 19) . "' ";
                    $graph .= ' GPRINT:DS1:LAST:%0.0lf ';
                    $graph .= ' GPRINT:DS1:AVERAGE:%0.0lf ';
                    $graph .= ' GPRINT:DS1:MAX:%0.0lf\\l ';
                }

                $rrd_filename = $this->rrdName('Open_tables');
                if (Rrd::checkRrdExists($rrd_filename)) {
                    $graph .= ' DEF:DS2=' . $rrd_filename . ':value:AVERAGE ';
                    $graph .= ' LINE1.25:DS2#' . $mixed_colours[4] . ":'" . str_pad(substr('Open_tables', 0, 19), 19) . "' ";
                    $graph .= ' GPRINT:DS2:LAST:%0.0lf ';
                    $graph .= ' GPRINT:DS2:AVERAGE:%0.0lf ';
                    $graph .= ' GPRINT:DS2:MAX:%0.0lf\\l ';
                }

                $rrd_filename = $this->rrdName('Table_locks_waited');
                $graph .= ' DEF:DS3=' . $rrd_filename . ':value:AVERAGE ';
                $graph .= ' LINE1.25:DS3#' . $mixed_colours[5] . ":'" . str_pad(substr('Table_locks_waited', 0, 19), 19) . "' ";
                $graph .= ' GPRINT:DS3:LAST:%0.0lf ';
                $graph .= ' GPRINT:DS3:AVERAGE:%0.0lf ';
                $graph .= ' GPRINT:DS3:MAX:%0.0lf\\l ';

                $rrd_filename = $this->rrdName('Threads_connected');
                if (Rrd::checkRrdExists($rrd_filename)) {
                    $graph .= ' DEF:DS4=' . $rrd_filename . ':value:AVERAGE ';
                    $graph .= ' LINE1.25:DS4#' . $mixed_colours[6] . ":'" . str_pad(substr('Threads_connected', 0, 19), 19) . "' ";
                    $graph .= ' GPRINT:DS4:LAST:%0.0lf ';
                    $graph .= ' GPRINT:DS4:AVERAGE:%0.0lf ';
                    $graph .= ' GPRINT:DS4:MAX:%0.0lf\\l ';
                }

                $rrd_filename = $this->rrdName('Threads_running');
                if (Rrd::checkRrdExists($rrd_filename)) {
                    $graph .= ' DEF:DS5=' . $rrd_filename . ':value:AVERAGE ';
                    $graph .= ' LINE1.25:DS5#' . $mixed_colours[7] . ":'" . str_pad(substr('Threads_running', 0, 19), 19) . "' ";
                    $graph .= ' GPRINT:DS5:LAST:%0.0lf ';
                    $graph .= ' GPRINT:DS5:AVERAGE:%0.0lf ';
                    $graph .= ' GPRINT:DS5:MAX:%0.0lf\\l ';
                }

                return $graph;
            }
        } elseif ($ds == 'mysqluptime') {
            $rrd_filename = $this->rrdName('Uptime');
            if (Rrd::checkRrdExists($rrd_filename)) {
                $graph .= ' DEF:DS0=' . $rrd_filename . ':value:LAST ';
                $graph .= ' CDEF:cuptime=DS0,86400,/';
                $graph .= " 'COMMENT:Days      Current  Minimum  Maximum  Average\\n'";
                $graph .= ' AREA:cuptime#EEEEEE:Uptime';
                $graph .= ' LINE1.25:cuptime#36393D:';
                $graph .= ' GPRINT:cuptime:LAST:%6.2lf  GPRINT:cuptime:MIN:%6.2lf';
                $graph .= ' GPRINT:cuptime:MAX:%6.2lf  GPRINT:cuptime:AVERAGE:%6.2lf\\l';

                return $graph;
            }
        } elseif ($ds == 'mysqlQcache') {
            $rrd_filename = $this->rrdName('Qcache_free_memory');
            if (Rrd::checkRrdExists($rrd_filename)) {
                $graph .= ' DEF:DS0=' . $rrd_filename . ':value:AVERAGE ';
                $graph .= ' LINE1.25:DS0#' . $mixed_colours[0] . ":'" . str_pad(substr('Qcache_free_memory', 0, 19), 19) . "' ";
                $graph .= ' GPRINT:DS0:LAST:%9.2lf%s ';
                $graph .= ' GPRINT:DS0:AVERAGE:%9.2lf%s ';
                $graph .= ' GPRINT:DS0:MAX:%9.2lf%s\\l ';
            }

            $rrd_filename = $this->rrdName('Qcache_hits');
            if (Rrd::checkRrdExists($rrd_filename)) {
                $graph .= ' DEF:DS1=' . $rrd_filename . ':value:AVERAGE ';
                $graph .= ' LINE1.25:DS1#' . $mixed_colours[1] . ":'" . str_pad(substr('Qcache_hits', 0, 19), 19) . "' ";
                $graph .= ' GPRINT:DS1:LAST:%9.2lf ';
                $graph .= ' GPRINT:DS1:AVERAGE:%9.2lf ';
                $graph .= ' GPRINT:DS1:MAX:%9.2lf\\l ';
            }

            $rrd_filename = $this->rrdName('Qcache_inserts');
            if (Rrd::checkRrdExists($rrd_filename)) {
                $graph .= ' DEF:DS2=' . $rrd_filename . ':value:AVERAGE ';
                $graph .= ' LINE1.25:DS2#' . $mixed_colours[2] . ":'" . str_pad(substr('Qcache_inserts', 0, 19), 19) . "' ";
                $graph .= ' GPRINT:DS2:LAST:%9.2lf ';
                $graph .= ' GPRINT:DS2:AVERAGE:%9.2lf ';
                $graph .= ' GPRINT:DS2:MAX:%9.2lf\\l ';
            }

            $rrd_filename = $this->rrdName('Qcache_lowmem_prune');
            if (Rrd::checkRrdExists($rrd_filename)) {
                $graph .= ' DEF:DS3=' . $rrd_filename . ':value:AVERAGE ';
                $graph .= ' LINE1.25:DS3#' . $mixed_colours[3] . ":'" . str_pad(substr('Qcache_lowmem_prune', 0, 19), 19) . "' ";
                $graph .= ' GPRINT:DS3:LAST:%9.2lf ';
                $graph .= ' GPRINT:DS3:AVERAGE:%9.2lf ';
                $graph .= ' GPRINT:DS3:MAX:%9.2lf\\l ';
            }

            $rrd_filename = $this->rrdName('Qcache_not_cached');
            if (Rrd::checkRrdExists($rrd_filename)) {
                $graph .= ' DEF:DS4=' . $rrd_filename . ':value:AVERAGE ';
                $graph .= ' LINE1.25:DS4#' . $mixed_colours[4] . ":'" . str_pad(substr('Qcache_not_cached', 0, 19), 19) . "' ";
                $graph .= ' GPRINT:DS4:LAST:%9.2lf ';
                $graph .= ' GPRINT:DS4:AVERAGE:%9.2lf ';
                $graph .= ' GPRINT:DS4:MAX:%9.2lf\\l ';
            }

            $rrd_filename = $this->rrdName('Qcache_queries_in_c');
            if (Rrd::checkRrdExists($rrd_filename)) {
                $graph .= ' DEF:DS5=' . $rrd_filename . ':value:AVERAGE ';
                $graph .= ' LINE1.25:DS5#' . $mixed_colours[5] . ":'" . str_pad(substr('Qcache_queries_in_c', 0, 19), 19) . "' ";
                $graph .= ' GPRINT:DS5:LAST:%9.2lf ';
                $graph .= ' GPRINT:DS5:AVERAGE:%9.2lf ';
                $graph .= ' GPRINT:DS5:MAX:%9.2lf\\l ';
            }

            return $graph;
        }

        return parent::graphRrdCommands($ds);
    }
}
