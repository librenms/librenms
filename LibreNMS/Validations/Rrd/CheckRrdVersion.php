<?php
/*
 * CheckRrdVersion.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Validations\Rrd;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Interfaces\Validation;
use LibreNMS\Interfaces\ValidationFixer;
use LibreNMS\Util\Version;
use LibreNMS\ValidationResult;
use Storage;

class CheckRrdVersion implements Validation, ValidationFixer
{
    public function validate(): ValidationResult
    {
        // Check that rrdtool config version is what we see
        $rrd_version = Version::get()->rrdtool();
        $config_version = Config::get('rrdtool_version');

        if (version_compare($config_version, '1.5.5', '<')
            && version_compare($config_version, $rrd_version, '>')
        ) {
            return ValidationResult::fail(
                trans('validation.validations.rrd.CheckRrdVersion.fail', ['config_version' => $config_version, 'installed_version' => $rrd_version]),
                trans('validation.validations.rrd.CheckRrdVersion.fix', ['version' => $config_version])
            )->setFixer(__CLASS__, is_writable(base_path('config.php')));
        }

        return ValidationResult::ok(trans('validation.validations.rrd.CheckRrdVersion.ok'));
    }

    public function enabled(): bool
    {
        return Config::has('rrdtool_version');
    }

    public function fix(): bool
    {
        try {
            $contents = Storage::disk('base')->get('config.php');

            $lines = array_filter(explode("\n", $contents), function ($line) {
                return ! Str::contains($line, ['$config[\'rrdtool_version\']', '$config["rrdtool_version"]']);
            });

            return Storage::disk('base')->put('config.php', implode("\n", $lines));
        } catch (FileNotFoundException $e) {
            return false;
        }
    }
}
