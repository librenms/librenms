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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Validations;

use App\Facades\LibrenmsConfig;
use Illuminate\Support\Str;
use LibreNMS\Util\Binaries;
use LibreNMS\Validator;

class Programs extends BaseValidation
{
    /** binaries located by the config system, which may move to Binaries */
    private const CONFIG_BINARIES = ['fping', 'fping6', 'ipmitool', 'nfdump', 'rrdtool', 'snmpbulkwalk', 'snmpget', 'snmpgetnext', 'snmptranslate', 'snmptrap', 'snmpwalk', 'virsh'];
    /** binaries already located by Binaries, a config setting may have been saved before */
    private const MOVED_BINARIES = ['cron', 'systemctl', 'traceroute'];
    private const REPORT_URL = 'https://github.com/librenms/librenms/issues/new?template=bug_report.yml&title=Binary+location+differs';

    /**
     * Validate this module.
     * To return ValidationResults, call ok, warn, fail, or result methods on the $validator
     *
     * @param  Validator  $validator
     */
    public function validate(Validator $validator): void
    {
        // Check programs
        $bins = ['fping', 'rrdtool', 'snmpwalk', 'snmpget', 'snmpgetnext', 'snmpbulkwalk'];
        foreach ($bins as $bin) {
            if (! ($cmd = $this->findExecutable($bin))) {
                $validator->fail(
                    "$bin location is incorrect or bin not installed.",
                    "Install $bin or manually set the path to $bin by placing the following in config.php: " .
                    "\$config['$bin'] = '/path/to/$bin';"
                );
            } elseif ($bin == 'fping') {
                $this->extraFpingChecks($validator, $cmd);
                $this->checkFping6($validator, $cmd);
            }
        }

        $this->checkBinaryLocations($validator);
    }

    /**
     * Early warning for binaries that are found somewhere else by Binaries than by the config system
     */
    public function checkBinaryLocations(Validator $validator): void
    {
        $binaries = resolve(Binaries::class);

        foreach ([...self::CONFIG_BINARIES, ...self::MOVED_BINARIES] as $bin) {
            $configured = $this->findExecutable($bin);
            if ($configured === false) {
                continue; // not installed, or set to a path that does not exist on this node
            }

            $detected = $binaries->find($bin);
            if ($detected !== null && realpath($detected) === realpath($configured)) {
                continue;
            }

            $moved = in_array($bin, self::MOVED_BINARIES);
            $overridable = array_key_exists($bin, (array) config('librenms.binaries'));
            $validator->warn(
                "$bin: the old lookup finds $configured, the new lookup finds " . ($detected ?? 'nothing') . ($moved ? ' and uses it.' : '. A future version will use the new lookup.'),
                ($overridable ? "To use $configured, set " . strtoupper($bin) . "_PATH=$configured in .env. " : '') . 'Please report this so we can see how many installs are affected: ' . self::REPORT_URL
            );
        }
    }

    public function checkFping6(Validator $validator, $fping)
    {
        $fping6 = $this->findExecutable('fping6');
        $fping6 = (! is_executable($fping6) && is_executable($fping)) ? "$fping -6" : $fping6;

        $validator->execAsUser("$fping6 ::1 2>&1", $output, $return);
        $output = implode(' ', $output);

        if ($return === 0 && $output == '::1 is alive') {
            return; // fping is working
        }

        if ($output == '::1 address not found') {
            $validator->warn('fping does not have IPv6 support?!?!');

            return;
        }

        if (Str::contains($output, '::1 is unreachable') || Str::contains($output, 'Address family not supported')) {
            $validator->warn('IPv6 is disabled on your server, you will not be able to add IPv6 devices.');

            return;
        }

        if (str_ends_with((string) $fping6, 'fping6')) {
            $this->failFping($validator, $fping6, $output);
        }
    }

    public function extraFpingChecks(Validator $validator, $cmd)
    {
        $validator->execAsUser("$cmd 127.0.0.1 2>&1", $output, $return);
        $output = implode(' ', $output);

        if ($return === 0 && $output == '127.0.0.1 is alive') {
            return; // fping is working
        }

        $this->failFping($validator, $cmd, $output);
    }

    private function failFping($validator, $cmd, $output)
    {
        $validator->info('fping FAILURES can be ignored if running LibreNMS in a jail without ::1. You may want to test it manually: fping ::1');
        $validator->fail(
            "$cmd could not be executed. $cmd must have CAP_NET_RAW capability (getcap) or suid. Selinux exclusions may be required.\n ($output)"
        );

        if ($getcap = $this->findExecutable('getcap')) {
            $getcap_out = shell_exec("$getcap $cmd");
            preg_match("#^$cmd (.*)$#", $getcap_out, $matches);

            if (empty($matches) || ! Str::contains($matches[1], ['cap_net_raw+ep', 'cap_net_raw=ep'])) {
                $validator->fail(
                    "$cmd should have CAP_NET_RAW!",
                    "setcap cap_net_raw+ep $cmd"
                );
            }
        } elseif (! (fileperms($cmd) & 2048)) {
            $validator->fail("$cmd should be suid!", "chmod u+s $cmd");
        }
    }

    public function findExecutable($bin)
    {
        $configured = LibrenmsConfig::get($bin);
        if (is_string($configured) && is_executable($configured)) {
            return $configured;
        }

        $located = LibrenmsConfig::locateBinary($bin);
        if (is_executable($located)) {
            return $located;
        }

        return false;
    }
}
