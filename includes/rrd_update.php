<?php
/**
 * rrd_rename.php
 *
 * Renames rrd files in a similar way to schema updates
 * -d debug
 * -f force
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

$init_modules = array();
require __DIR__ . '/init.php';

$options = getopt('df');
$debug = isset($options['d']);
if (isset($options['f'])) {
    $rrd_rev = 0;
}

// check if we have rrds to work with
if (!has_rrds()) {
    echo "This host does not have access to rrd files\n";
    exit(1);
}

// get current rrd revision
$rrd_rev = @dbFetchCell("SELECT `version` FROM `versions` WHERE `component`='rrd' ORDER BY `version` DESC LIMIT 1");
$insert = is_null($rrd_rev);

$new_rrd_rev = $rrd_rev;
d_echo("Current RRD Rename revision $rrd_rev\n");

// get list of rrd rename operations
$dir = $config['install_dir'] . '/schema/rrd/';

try {
    $iterator = new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS);
    foreach ($iterator as $file) {
        /** @var $file SplFileInfo */
        $file_rev = $file->getBasename();

        if ($file->isFile() && $file->getExtension() == 'php' && intval($file_rev) > intval($rrd_rev)) {
            require $file->getRealPath();  // include the operation file, should define $rrd_operation
            echo "Rename operation $file_rev: " . $rrd_operation->getDesc() . PHP_EOL;
            $rrd_operation->run();
            $new_rrd_rev = $file_rev;
        }
    }

    if ($new_rrd_rev > $rrd_rev) {  // insert/update the rrd rename version, if needed
        if ($insert) {
            dbInsert(array('component' => 'rrd', 'version' => $new_rrd_rev), 'versions');
        } else {
            dbUpdate(array('component' => 'rrd', 'version' => $new_rrd_rev), 'versions');
        }
        d_echo("Updated to revision $new_rrd_rev.\n");
    } else {
        d_echo("No RRD renames needed.\n");
    }
} catch (Exception $e) {
    c_echo('%rRename failed%n: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
