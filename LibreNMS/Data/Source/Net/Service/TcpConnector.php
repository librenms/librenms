<?php
/**
 * TcpConnector.php
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
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Source\Net\Service;

class TcpConnector extends BaseConnector
{
    public function __construct(string $ip, int $port = 80)
    {
        parent::__construct($ip, $port);
    }

    public function connect(): bool
    {
        $this->createSocket(SOCK_STREAM, SOL_TCP);

        $result = socket_connect($this->socket, $this->ip, $this->port);
        $this->waitForWrite();

        if ($result === false) {
            $error = socket_last_error($this->socket);
            // In non-blocking mode, EINPROGRESS SOCKET_EALREADY (or EAGAIN) is expected
            if ($error !== SOCKET_EINPROGRESS && $error !== SOCKET_EALREADY && $error !== SOCKET_EAGAIN) {
                $this->close();
                throw new \RuntimeException("Failed to connect to $this " . socket_strerror($error));
            }
        }

        return $result;
    }

    public function isServiceAvailable(): bool
    {
        $soError = socket_get_option($this->socket, SOL_SOCKET, SO_ERROR);

        $peerIp = null;
        if($soError === 0 && socket_getpeername($this->socket, $peerIp) && $peerIp === $this->ip) {
            return true;
        }

        throw new \RuntimeException("Failed to verify peer for $this " . socket_strerror($soError));
    }
}
