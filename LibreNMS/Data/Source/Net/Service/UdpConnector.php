<?php
/**
 * UdpConnector.php
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

use Illuminate\Support\Facades\Log;

class UdpConnector extends BaseConnector
{
    public function __construct(string $ip, int $port, private UdpCodec $requestMessage)
    {
        parent::__construct($ip, $port);
    }

    public function connect(): bool
    {
        $this->createSocket(SOCK_DGRAM, SOL_UDP);

        $payload = $this->requestMessage->getPayload();

        $bytesSent = socket_sendto($this->socket, $payload, strlen($payload), 0, $this->ip, $this->port);
        $this->waitForRead();

        if ($bytesSent === false) {
            throw new \RuntimeException("Failed to send UDP packet to $this " . socket_strerror(socket_last_error()));
        }

        return true;
    }

    public function isServiceAvailable(): bool
    {
        $response = '';
        $from = '';
        $portFrom = 0;

        $bytesReceived = socket_recvfrom($this->socket, $response, 4096, 0, $from, $portFrom);

        if ($bytesReceived > 0 && $from === $this->ip) {
            // Basic validation: check if response is valid SNMP
            if ($this->requestMessage->validateResponse($response)) {
                Log::info("Received valid UDP response from $this->ip");
                return true;
            }
        }

        throw new \RuntimeException("Failed to verify peer for $this " . socket_strerror(socket_last_error($this->socket)));
    }
}
