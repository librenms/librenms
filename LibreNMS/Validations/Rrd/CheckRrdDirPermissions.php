<?php
/*
 * CheckRrddirPermissions.php
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

use LibreNMS\Config;
use LibreNMS\Interfaces\Validation;
use LibreNMS\ValidationResult;

class CheckRrdDirPermissions implements Validation
{
    /**
     * @inheritDoc
     */
    public function validate(): ValidationResult
    {
        $rrd_dir = Config::get('rrd_dir');

        $dir_stat = stat($rrd_dir);
        if ($dir_stat[4] == 0 || $dir_stat[5] == 0) {
            return ValidationResult::warn(trans('validation.validations.rrd.CheckRrdDirPermissions.fail_root'),
                sprintf('chown %s:%s %s', Config::get('user'), Config::get('group'), $rrd_dir)
            );
        }

        if (substr(sprintf('%o', fileperms($rrd_dir)), -3) != 775) {
            return ValidationResult::warn(trans('validation.validations.rrd.CheckRrdDirPermissions.fail_mode'), "chmod 775 $rrd_dir");
        }

        return ValidationResult::ok(trans('validation.validations.rrd.CheckRrdDirPermissions.ok'));
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return ! Config::get('rrdcached');
    }
}
