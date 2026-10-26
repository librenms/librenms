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

use App\Models\Config;
use Illuminate\Support\Str;
use LibreNMS\DB\Eloquent;
use LibreNMS\Util\Binaries;
use LibreNMS\Util\Version;
use LibreNMS\Validator;

class Programs extends BaseValidation
{
    /** older versions have no -4/-6 options, IPv6 needs the separate fping6 binary */
    public const FPING_MIN_VERSION = '4.0';
    /** binaries needed to poll devices */
    private const REQUIRED_BINARIES = ['fping', 'rrdtool', 'snmpwalk', 'snmpget', 'snmpgetnext', 'snmpbulkwalk'];

    /**
     * Validate this module.
     * To return ValidationResults, call ok, warn, fail, or result methods on the $validator
     *
     * @param  Validator  $validator
     */
    public function validate(Validator $validator): void
    {
        $binaries = resolve(Binaries::class);

        foreach (self::REQUIRED_BINARIES as $bin) {
            $cmd = $binaries->find($bin);
            if ($cmd === null) {
                $validator->fail(
                    "$bin location is incorrect or bin not installed.",
                    "Install $bin or, if it is installed somewhere else or under another name, add " . self::envVariable($bin) . "=/path/to/$bin to .env."
                );
            } elseif ($bin == 'fping') {
                $this->checkFpingVersion($validator, Version::get()->fping());
                $this->extraFpingChecks($validator, $cmd);
                $this->checkFping6($validator, $cmd);
            }
        }

        $this->checkLegacySettings($validator);
    }

    /**
     * Binary paths set in the LibreNMS config are still used, but support for them will be removed
     */
    public function checkLegacySettings(Validator $validator): void
    {
        $binaries = resolve(Binaries::class);

        foreach (Binaries::LEGACY_SETTINGS as $bin => $name) {
            if ($binaries->configured($bin) !== null) {
                continue; // set in .env, the setting is not used
            }

            $setting = $binaries->legacySetting($bin);
            if ($setting === null) {
                continue;
            }

            $found = $binaries->search($bin);
            if ($found !== null && realpath($found) === realpath($setting)) {
                continue; // found without the setting
            }

            // config.php is loaded after the database and overrides it
            $stored = Eloquent::isConnected() ? Config::query()->where('config_name', $name)->first()?->config_value : null;
            $remove = $stored === $setting
                ? "remove the setting with: lnms config:set $name"
                : "remove \$config['$name'] from config.php";

            if (array_key_exists($bin, (array) config('librenms.binaries'))) {
                $fix = "To keep using $setting, add " . self::envVariable($bin) . "=$setting to .env, then $remove.";
            } else {
                $fix = $found === null ? "Install $bin in a standard location, then $remove." : '';
            }

            if ($found !== null) {
                $fix .= " To use $found, $remove.";
            }

            $validator->fail(
                "$name is set to $setting in the LibreNMS config. Support for this setting will be removed, then " . ($found === null ? "$bin will not be found." : "$found will be used."),
                trim($fix)
            );
        }
    }

    public function checkFpingVersion(Validator $validator, string $version): void
    {
        if ($version === '') {
            return; // version unknown, the other fping checks will report real problems
        }

        if (version_compare($version, self::FPING_MIN_VERSION, '<')) {
            $validator->warn(
                "fping $version is older than " . self::FPING_MIN_VERSION . ' and does not support -4/-6. A future version of LibreNMS will require fping ' . self::FPING_MIN_VERSION . ' or newer.',
                'Update fping to version ' . self::FPING_MIN_VERSION . ' or newer.'
            );
        }
    }

    public function checkFping6(Validator $validator, $fping)
    {
        $fping6 = resolve(Binaries::class)->find('fping6') ?? "$fping -6";

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

        if ($getcap = resolve(Binaries::class)->find('getcap')) {
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

    private static function envVariable(string $bin): string
    {
        return strtoupper($bin) . '_PATH';
    }
}
