<?php
/**
 * NtpConnector.php
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

class NtpConnector extends BaseConnector
{
    public function __construct(string $ip, int $port = 123)
    {
        parent::__construct($ip, $port);
    }

    public function connect(): bool
    {
        $this->createSocket(SOCK_DGRAM, SOL_UDP);

        $ntpPacket = chr(0x1B) . str_repeat(chr(0x00), 47);

        // Send the packet immediately, as UDP is connectionless
        $bytesSent = socket_sendto($this->socket, $ntpPacket, strlen($ntpPacket), 0, $this->ip, $this->port);
        $this->waitForRead();

        if ($bytesSent === false) {
            throw new \RuntimeException("Failed to send NTP packet to $this ".socket_strerror(socket_last_error()));
        }

        return true;
    }

    public function isServiceAvailable(): bool
    {
        $response = '';
        $from = '';
        $portFrom = 0;
        // Attempt to read the response now that we know data is available
        $bytesReceived = socket_recvfrom($this->socket, $response, 255, 0, $from, $portFrom);

        if ($bytesReceived > 0 && strlen((string) $response) == 48 && $from === $this->ip) {
            Log::info("Received valid NTP response from the first responsive IP: $this->ip");
            return true;
        }

        throw new \RuntimeException("Failed to verify peer for $this " . socket_strerror(socket_last_error($this->socket)));
    }

}
