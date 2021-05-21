<?php
/**
 * IPMIClient.php
 *
 * IPMI Client
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
 * @copyright  2021 Trym Lund Flogard
 * @author     Trym Lund Flogard <trym@flogard.no>
 */

namespace LibreNMS\IPMI;

class IPMIClient
{
    private $ipmiToolPath;
    private $host;
    private $user;
    private $password;
    private $privLvl = 'USER';
    private $interface = NULL;

    public function __construct($ipmiToolPath, $host, $user, $password)
    {
        $this->ipmiToolPath = $ipmiToolPath;
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
    }

    public function getInterface($interface)
    {
        return $this->interface;
    }

    public function setInterface($interface)
    {
        $this->interface = $interface;
    }

    public function getSensors()
    {
        return $this->send('sensor');
    }

    public function getSensorDataRepository()
    {
        return $this->send('sdr');
    }

    public function getNodeManagerPowerStatistics()
    {
        return $this->send('nm statistics power', 0, 0x2c);
    }

    private static function packHex(int $value)
    {
        return "0x" + bin2hex(pack('c', $value));
    }

    private function send($command, int $channel = 0, int $transit_address = 0)
    {
        $cmd = [$this->ipmiToolPath];
        if ($this->host != 'localhost') {
            array_push($cmd, '-H', $this->host, '-U', $this->user, '-P', $this->password, '-L', $this->privLvl);
        }

        array_push($cmd, '-I', $this->interface, '-b', IPMIClient::packHex($channel), '-t', IPMIClient::packHex($transit_address));
        array_push($cmd, $command);

        return explode(PHP_EOL, external_exec($cmd));
    }
}
