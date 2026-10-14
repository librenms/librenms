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

use App\Facades\Binaries;
use App\Facades\LibrenmsConfig;
use LibreNMS\Interfaces\Validation;
use LibreNMS\ValidationResult;

class CheckRrdVersion implements Validation
{
    const MIN_VERSION = '1.5.5';

    public function validate(): ValidationResult
    {
        $rrd_version = Binaries::rrdtoolVersion();

        if (version_compare($rrd_version, self::MIN_VERSION, '<')) {
            return ValidationResult::fail(
                trans('validation.validations.rrd.CheckRrdVersion.fail', ['installed_version' => $rrd_version])
            );
        }

        if (LibrenmsConfig::get('rrdcached')) {
            $rrdcached_version = (string) LibrenmsConfig::get('rrdcached_version');

            if ($rrdcached_version !== '' && version_compare($rrdcached_version, self::MIN_VERSION, '<')) {
                return ValidationResult::fail(
                    trans('validation.validations.rrd.CheckRrdVersion.fail_rrdcached', ['version' => $rrdcached_version]),
                    trans('validation.validations.rrd.CheckRrdVersion.fix_rrdcached')
                );
            }

            // rrdtool_version used to limit features to an older rrdcached server, it is no longer used
            $legacy_version = (string) LibrenmsConfig::get('rrdtool_version');
            if ($rrdcached_version === '' && $legacy_version !== '' && version_compare($legacy_version, $rrd_version, '<')) {
                return ValidationResult::warn(
                    trans('validation.validations.rrd.CheckRrdVersion.legacy', ['version' => $legacy_version, 'installed_version' => $rrd_version]),
                    trans('validation.validations.rrd.CheckRrdVersion.fix_legacy', ['version' => $legacy_version, 'installed_version' => $rrd_version])
                );
            }
        }

        return ValidationResult::ok(trans('validation.validations.rrd.CheckRrdVersion.ok'));
    }

    public function enabled(): bool
    {
        return Binaries::find('rrdtool') !== null;
    }
}
