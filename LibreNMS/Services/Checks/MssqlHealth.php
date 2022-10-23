<?php
/*
 * MssqlCheck.php
 *
 * check_mssql_health
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

class MssqlHealth extends \LibreNMS\Services\DefaultServiceCheck
{
    protected $target_option = '--server';

    public function getMetrics(string $metric_text): array
    {
        // bugfix storage size is lowercase
        $metric_text = str_replace('kb', 'KB', $metric_text);

        return parent::getMetrics($metric_text);
    }
}
