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
use LibreNMS\Validator;

class Programs extends BaseValidation
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
        $bins = array('fping', 'fping6', 'rrdtool', 'snmpwalk', 'snmpget', 'snmpgetnext', 'snmpbulkwalk');
        foreach ($bins as $bin) {
            if (!($cmd = $this->findExecutable($bin))) {
                $validator->fail(
                    "$bin location is incorrect or bin not installed.",
                    "Install $bin or manually set the path to $bin by placing the following in config.php: " .
                    "\$config['$bin'] = '/path/to/$bin';"
                );
            } elseif (in_array($bin, array('fping', 'fping6'))) {
                $this->extraFpingChecks($validator, $bin, $cmd);
            }
        }
    }

    public function extraFpingChecks(Validator $validator, $bin, $cmd)
    {
        $target = ($bin == 'fping' ? '127.0.0.1' : '::1');
        $validator->execAsUser("$cmd $target 2>&1", $output, $return);
        $output = implode(" ", $output);

        if ($return === 0 && $output == "$target is alive") {
            return; // fping is working
        }

        if ($output == '::1 address not found') {
            $validator->warn("fping6 does not have IPv6 support?!?!");
            return;
        }

        if (str_contains($output, '::1 is unreachable') || str_contains($output, 'Address family not supported')) {
            $validator->warn("IPv6 is disabled on your server, you will not be able to add IPv6 devices.");
            return;
        }

        $validator->fail(
            "$bin could not be executed. $bin must have CAP_NET_RAW capability (getcap) or suid. Selinux exlusions may be required.\n ($output)"
        );

        if ($getcap = $this->findExecutable('getcap')) {
            $getcap_out = shell_exec("$getcap $cmd");
            preg_match("#^$cmd = (.*)$#", $getcap_out, $matches);

            if (is_null($matches) || !str_contains($matches[1], 'cap_net_raw+ep')) {
                $validator->fail(
                    "$bin should have CAP_NET_RAW!",
                    "setcap cap_net_raw+ep $cmd"
                );
            }
        } elseif (!(fileperms($cmd) & 2048)) {
            $validator->fail("$bin should be suid!", "chmod u+s $cmd");
        }
    }

    public function findExecutable($bin)
    {
        if (is_executable(Config::get($bin))) {
            return Config::get($bin);
        }

        $located = Config::locateBinary($bin);
        if (is_executable($located)) {
            return $located;
        }

        return false;
    }
}
