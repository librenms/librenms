<?php
/**
 * Programs.php
 *
 * Check that external programs exist and are executable.
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
use LibreNMS\Interfaces\ValidationGroup;
use LibreNMS\Validator;

class Programs implements ValidationGroup
{
    /**
     * Validate this module.
     * To return ValidationResults, call ok, warn, fail, or result methods on the $validator
     *
     * @param Validator $validator
     */
    public function validate(Validator $validator)
    {
        // Check programs
        $bins = array('fping', 'fping6', 'rrdtool', 'snmpwalk', 'snmpget', 'snmpbulkwalk');
        foreach ($bins as $bin) {
            if (!($cmd = $this->findExecutable($bin))) {
                $validator->fail(
                    "$bin location is incorrect or bin not installed.",
                    "Install $bin or manually set the path to $bin by placing the following in config.php: " .
                    "\$config['$bin'] = '/path/to/$bin';"
                );
            } elseif (in_array($bin, array('fping', 'fping6'))) {
                if ($validator->getUsername() == 'root' && ($getcap = $this->findExecutable('getcap'))) {
                    if (!str_contains(shell_exec("$getcap $cmd"), "$cmd = cap_net_raw+ep")) {
                        $validator->fail(
                            "$bin should have CAP_NET_RAW!",
                            "getcap c $cmd"
                        );
                    }
                } elseif (!(fileperms($cmd) & 2048)) {
                    $msg = "$bin should be suid!";
                    $fix = "chmod u+s $cmd";
                    if ($validator->getUsername() == 'root') {
                        $msg .= ' (Note: suid may not be needed if CAP_NET_RAW is set, which requires root to check)';
                        $validator->warn($msg, $fix);
                    } else {
                        $validator->fail($msg, $fix);
                    }
                }
            }
        }
    }

    public function findExecutable($bin)
    {
        if (is_executable(Config::get($bin))) {
            return Config::get($bin);
        }

        $path_dirs = explode(':', getenv('PATH'));
        foreach ($path_dirs as $dir) {
            $file = "$dir/$bin";
            if (is_executable($file)) {
                return $file;
            }
        }

        return false;
    }

    /**
     * Returns if this test should be run by default or not.
     *
     * @return bool
     */
    public function isDefault()
    {
        return true;
    }
}
