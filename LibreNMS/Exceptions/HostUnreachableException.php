<?php
/**
 * HostUnreachableException.php
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

class HostUnreachableException extends \Exception
{
    protected $reasons = [];

    public function __toString()
    {
        $string = __CLASS__ . ": [{$this->code}]: {$this->message}\n";
        foreach ($this->reasons as $reason) {
            $string .= "  $reason\n";
        }

        return $string;
    }

    /**
     * Add additional reasons
     *
     * @param  string  $snmpVersion
     * @param  string  $credentials
     */
    public function addReason(string $snmpVersion, string $credentials)
    {
        $vars = [
            'version' => $snmpVersion,
            'credentials' => $credentials,
        ];

        if ($snmpVersion == 'v3') {
            $this->reasons[] = trans('exceptions.host_unreachable.no_reply_credentials', $vars);
        } else {
            $this->reasons[] = trans('exceptions.host_unreachable.no_reply_community', $vars);
        }
    }

    /**
     * Get the reasons
     *
     * @return array
     */
    public function getReasons()
    {
        return $this->reasons;
    }
}
