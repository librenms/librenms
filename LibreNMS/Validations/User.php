<?php
/**
 * User.php
 *
 * Check that user is set properly and we are running as the correct user.  Check that user is the owner of install_dir.
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
use LibreNMS\ValidationResult;
use LibreNMS\Validator;

class User extends BaseValidation
{
    /**
     * Validate this module.
     * To return ValidationResults, call ok, warn, fail, or result methods on the $validator
     *
     * @param Validator $validator
     */
    public function validate(Validator $validator)
    {
        // Check we are running this as the root user
        $username = $validator->getUsername();
        $lnms_username = Config::get('user');
        $lnms_groupname = Config::get('group', $lnms_username); // if group isn't set, fall back to user

        if (!($username === 'root' || $username === $lnms_username)) {
            if (isCli()) {
                $validator->fail('You need to run this script as root' .
                    (Config::has('user') ? ' or ' . $lnms_username : ''));
            } elseif (function_exists('posix_getgrnam')) {
                $lnms_group = posix_getgrnam($lnms_groupname);
                if (!in_array($username, $lnms_group['members'])) {
                    $validator->fail(
                        "Your web server or php-fpm is not running as user '$lnms_username' or in the group '$lnms_groupname''",
                        "usermod -a -G $lnms_groupname $username"
                    );
                }
            }
        }


        // Let's test the user configured if we have it
        if (Config::has('user')) {
            $dir = Config::get('install_dir');

            $find_result = rtrim(`find $dir \! -user $lnms_username -o \! -group $lnms_groupname &> /dev/null`);
            if (!empty($find_result)) {
                // Ignore the two logs that may be created by the
                $files = array_diff(explode(PHP_EOL, $find_result), array(
                    "$dir/logs/error_log",
                    "$dir/logs/access_log",
                ));

                if (!empty($files)) {
                    $result = ValidationResult::fail(
                        "We have found some files that are owned by a different user than $lnms_username, this " .
                        'will stop you updating automatically and / or rrd files being updated causing graphs to fail.'
                    )
                        ->setFix("chown -R $lnms_username:$lnms_groupname $dir")
                        ->setList('Files', $files);

                    $validator->result($result);
                }
            }
        } else {
            $validator->warn("You don't have \$config['user'] set, this most likely needs to be set to librenms");
        }

        // check permissions
        $rrd_dir = Config::get('rrd_dir');
        if (!check_file_permissions($rrd_dir, '660')) {
            $validator->fail("The rrd folder has improper permissions.", "chmod ug+rw $rrd_dir");
        }

        $log_dir = Config::get('log_dir');
        if (!check_file_permissions($log_dir, '660')) {
            $validator->fail("The log folder has improper permissions.", "chmod ug+rw $log_dir");
        }
    }
}
