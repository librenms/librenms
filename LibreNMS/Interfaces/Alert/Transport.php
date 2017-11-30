<?php
/**
 * ValidationGroup.php
 *
 * An interface for the transport of alerts.
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

namespace LibreNMS\Interfaces\Alert;

interface Transport
{
    /**
     * Gets called when an alert is sent
     *
     * @param $obj array An array created by DescribeAlert
     * @param $opts array|true The options from $config['alert']['transports'][$transport]
     * @return bool Returns if the call was successful
     */
    public function call($obj, $opts);
}
