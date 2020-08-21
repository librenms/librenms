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

use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Util\EnvHelper;
use LibreNMS\Util\Git;
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
        $lnms_username = \config('librenms.user');
        $lnms_groupname = \config('librenms.group');

        if (!($username === 'root' || $username === $lnms_username)) {
            if (isCli()) {
                $validator->fail("You need to run this script as $lnms_username or root");
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

        // skip if docker image
        if (EnvHelper::librenmsDocker()) {
            return;
        }

        # if no git, then we probably have different permissions by design
        if (!Git::repoPresent()) {
            return;
        }

        // Let's test the user configured if we have it
        if ($lnms_username) {
            $dir = Config::get('install_dir');
            $log_dir = Config::get('log_dir', "$dir/logs");
            $rrd_dir = Config::get('rrd_dir', "$dir/rrd");

            // generic fix
            $fix = [
                "sudo chown -R $lnms_username:$lnms_groupname $dir",
                "sudo setfacl -d -m g::rwx $rrd_dir $log_dir $dir/bootstrap/cache/ $dir/storage/",
                "sudo chmod -R ug=rwX $rrd_dir $log_dir $dir/bootstrap/cache/ $dir/storage/",
            ];

            $find_result = rtrim(`find $dir \! -user $lnms_username -o \! -group $lnms_groupname 2> /dev/null`);
            if (!empty($find_result)) {
                // Ignore files created by the webserver
                $ignore_files = array(
                    "$log_dir/error_log",
                    "$log_dir/access_log",
                    "$dir/bootstrap/cache/",
                    "$dir/storage/framework/cache/",
                    "$dir/storage/framework/sessions/",
                    "$dir/storage/framework/views/",
                    "$dir/storage/debugbar/",
                    "$dir/.pki/", // ignore files/folders created by setting the librenms home directory to the install directory
                );

                $files = array_filter(explode(PHP_EOL, $find_result), function ($file) use ($ignore_files) {
                    if (Str::startsWith($file, $ignore_files)) {
                        return false;
                    }

                    return true;
                });

                if (!empty($files)) {
                    $result = ValidationResult::fail(
                        "We have found some files that are owned by a different user than $lnms_username, this " .
                        'will stop you updating automatically and / or rrd files being updated causing graphs to fail.'
                    )
                        ->setFix($fix)
                        ->setList('Files', $files);

                    $validator->result($result);
                    return;
                }
            }
        } else {
            $validator->warn("You don't have LIBRENMS_USER set, this most likely needs to be set to librenms");
        }
    }
}
