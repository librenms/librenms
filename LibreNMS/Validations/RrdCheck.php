<?php
/**
 * RrdCheck.php
 *
 * Scan RRD files for errors.
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Validations;

use LibreNMS\Config;
use LibreNMS\RRDRecursiveFilterIterator;
use LibreNMS\Validator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class RrdCheck extends BaseValidation
{
    protected static $RUN_BY_DEFAULT = false;

    /**
     * Validate this module.
     * To return ValidationResults, call ok, warn, fail, or result methods on the $validator
     *
     * @param Validator $validator
     */
    public function validate(Validator $validator)
    {
        // Loop through the rrd_dir
        $rrd_directory = new RecursiveDirectoryIterator(Config::get('rrd_dir'));
        // Filter out any non rrd files
        $rrd_directory_filter = new RRDRecursiveFilterIterator($rrd_directory);
        $rrd_iterator = new RecursiveIteratorIterator($rrd_directory_filter);
        $rrd_total = iterator_count($rrd_iterator);
        $rrd_iterator->rewind(); // Rewind iterator in case iterator_count left iterator in unknown state

        echo "\nScanning " . $rrd_total . " rrd files in " . Config::get('rrd_dir') . "...\n";

        // Count loops so we can push status to the user
        $loopcount = 0;
        $screenpad = 0;

        foreach ($rrd_iterator as $filename => $file) {
            $rrd_test_result = rrdtest($filename, $output, $error);

            $loopcount++;
            if (($loopcount % 50) == 0) {
                //This lets us update the previous status update without spamming in most consoles
                echo "\033[" . $screenpad . "D";
                $test_status = 'Status: ' . $loopcount . '/' . $rrd_total;
                echo $test_status;
                $screenpad = strlen($test_status);
            }

            // A non zero result means there was some kind of error
            if ($rrd_test_result > 0) {
                echo "\033[" . $screenpad . "D";
                $validator->fail('Error parsing "' . $filename . '" RRD ' . trim($error));
                $screenpad = 0;
            }
        }

        echo "\033[" . $screenpad . "D";
        echo "Status: " . $loopcount . "/" . $rrd_total . " - Complete\n";
    }
}
