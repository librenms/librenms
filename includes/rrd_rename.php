<?php
/**
 * rrd_rename.php
 *
 * Renames rrd files in a similar way to schema updates
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

include 'includes/defaults.inc.php';
include 'config.php';
include 'includes/definitions.inc.php';
include 'includes/functions.php';

$insert = false;
$starting_rrd_rev = @dbFetchCell('SELECT `version` FROM `rrd_rename` ORDER BY `version` DESC LIMIT 1');

if (!$starting_rrd_rev) {
    $starting_rrd_rev = 0;
}
$current_rrd_rev = $starting_rrd_rev;

$dir = $config['install_dir'] . '/schema/rrd/';
echo $dir . PHP_EOL;
$files = array_diff(scandir($dir), array('..', '.'));

foreach ($files as $file) {
    $file_rev = substr($file, 0, strpos($file, '.'));

    if (intval($file_rev) > intval($starting_rrd_rev)) {
        echo "Rename operation $file_rev\n";
        require "$dir/$file";
        $renamer->run();
        $current_rrd_rev = $file_rev;
    }
}


if ($starting_rrd_rev == 0) {
    dbInsert(array('version' => $current_rrd_rev), 'rrd_rename');
} else {
    dbUpdate(array('version' => $current_rrd_rev), 'rrd_rename');
}
