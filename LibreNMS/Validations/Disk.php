<?php
/**
 * Disk.php
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

class Disk extends BaseValidation
{
    /**
     * Validate this module.
     * To return ValidationResults, call ok, warn, fail, or result methods on the $validator
     *
     * @param Validator $validator
     */
    public function validate(Validator $validator)
    {
        // Disk space and permission checks
        $temp_dir = Config::get('temp_dir');
        if (substr(sprintf('%o', fileperms($temp_dir)), -3) != 777) {
            $validator->warn("Your tmp directory ($temp_dir) " .
                "is not set to 777 so graphs most likely won't be generated");
        }

        $rrd_dir = Config::get('rrd_dir');
        $space_check = (disk_free_space($rrd_dir) / 1024 / 1024);
        if ($space_check < 512 && $space_check > 1) {
            $validator->warn("Disk space where $rrd_dir is located is less than 512Mb");
        }

        if ($space_check < 1) {
            $validator->fail("Disk space where $rrd_dir is located is empty!!!");
        }
    }
}
