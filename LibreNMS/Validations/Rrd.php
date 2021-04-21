<?php
/**
 * Rrd.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Validations;

use LibreNMS\Config;
use LibreNMS\Validator;

class Rrd extends BaseValidation
{
    /**
     * Validate this module.
     * To return ValidationResults, call ok, warn, fail, or result methods on the $validator
     *
     * @param Validator $validator
     */
    public function validate(Validator $validator)
    {
        $versions = $validator->getVersions();

        // Check that rrdtool config version is what we see
        if (Config::has('rrdtool_version')
            && version_compare(Config::get('rrdtool_version'), '1.5.5', '<')
            && version_compare(Config::get('rrdtool_version'), $versions['rrdtool_ver'], '>')
        ) {
            $validator->fail(
                'The rrdtool version you have specified is newer than what is installed.',
                "Either comment out \$config['rrdtool_version'] = '" .
                Config::get('rrdtool_version') . "'; or set \$config['rrdtool_version'] = '{$versions['rrdtool_ver']}';"
            );
        }

        if (Config::get('rrdcached')) {
            self::checkRrdcached($validator);
        } else {
            $rrd_dir = Config::get('rrd_dir');

            $dir_stat = stat($rrd_dir);
            if ($dir_stat[4] == 0 || $dir_stat[5] == 0) {
                $validator->warn('Your RRD directory is owned by root, please consider changing over to user a non-root user');
            }

            if (substr(sprintf('%o', fileperms($rrd_dir)), -3) != 775) {
                $validator->warn('Your RRD directory is not set to 0775', "chmod 775 $rrd_dir");
            }
        }
    }

    public static function checkRrdcached(Validator $validator)
    {
        [$host,$port] = explode(':', Config::get('rrdcached'));
        if ($host == 'unix') {
            // Using socket, check that file exists
            if (! file_exists($port)) {
                $validator->fail("$port doesn't appear to exist, rrdcached test failed");
            }
        } else {
            $connection = @fsockopen($host, (int) $port);
            if (is_resource($connection)) {
                fclose($connection);
            } else {
                $validator->fail('Cannot connect to rrdcached instance');
            }
        }
    }
}
