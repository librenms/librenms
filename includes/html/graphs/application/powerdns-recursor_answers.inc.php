<?php
/**
 * powerdns-recursor_answers.inc.php
 *
 * Graph of answers
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
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

include 'powerdns-recursor.inc.php';

$colours = 'oranges';
$unit_text = 'Answers/sec';
$print_total = true;

if (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_list = array(
        array(
            'ds' => 'answers0-1',
            'filename' => $rrd_filename,
            'descr' => '0-1ms',
        ),
        array(
            'ds' => 'answers1-10',
            'filename' => $rrd_filename,
            'descr' => '1-10ms',
        ),
        array(
            'ds' => 'answers10-100',
            'filename' => $rrd_filename,
            'descr' => '10-100ms',
        ),
        array(
            'ds' => 'answers100-1000',
            'filename' => $rrd_filename,
            'descr' => '100-1000ms',
        ),
        array(
            'ds' => 'answers-slow',
            'filename' => $rrd_filename,
            'descr' => '>1s',
        ),
    );
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_multi.inc.php';
