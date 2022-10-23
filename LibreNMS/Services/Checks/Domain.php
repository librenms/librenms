<?php
/*
 * Domain.php
 *
 * check_domain
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

namespace LibreNMS\Services\Checks;

use LibreNMS\Util\Validate;

class Domain extends \LibreNMS\Services\DefaultServiceCheck
{
    public function hasDefaults(): array
    {
        return [
            '-d' => trans('service.check_params.domain.-d.description'),
            '-c' => trans('service.check_params.domain.-c.description'),
            '-w' => trans('service.check_params.domain.-w.description'),
        ];
    }

    public function getDefault(string $flag): string
    {
        switch ($flag) {
            case '-d':
                return $this->service->service_ip ?: Validate::hostname($this->service->device->hostname) ? $this->service->device->hostname : '';
            case '-c':
                return 10;
            case '-w':
                return 30;
        }

        return parent::getDefault($flag);
    }
}
