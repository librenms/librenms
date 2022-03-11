<?php
/**
 * HostUnreachablePingException.php
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
 *
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Exceptions;

use LibreNMS\Util\IP;

class HostUnreachablePingException extends HostUnreachableException
{
    /**
     * @var string
     */
    public $hostname;
    /**
     * @var string
     */
    public $ip;

    public function __construct(string $hostname)
    {
        $this->hostname = $hostname;
        $this->ip = gethostbyname($hostname);

        $message = trans('exceptions.host_unreachable.unpingable', [
            'hostname' => $hostname,
            'ip' => IP::isValid($this->ip) ? $this->ip : trans('exceptions.host_unreachable.unresolvable'),
        ]);

        parent::__construct($message);
    }
}
