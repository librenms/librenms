<?php
/**
 * IcmpConnector.php
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

class IcmpConnector extends BaseConnector
{

    public function __construct(string $ip, int $port = 0)
    {
        parent::__construct($ip, $port);
    }

    public function connect(): bool
    {
        $this->createSocket(SOCK_RAW, 1);

        if (! socket_connect($this->socket, $this->ip, $this->port)) {
            throw new \RuntimeException("Failed to connect to $this ".socket_strerror(socket_last_error($this->socket)));
        }

        $packet = $this->createPacket();
        $bytesSent = socket_send($this->socket, $packet, strlen($packet), 0);
        $this->waitForRead();

        if ($bytesSent === false) {
            throw new \RuntimeException("Failed to send NTP packet to $this ".socket_strerror(socket_last_error($this->socket)));
        }

        return true;
    }

    public function isServiceAvailable(): bool
    {
        if (socket_read($this->socket, 255) !== false) {
            // Process the response (e.g., parse for TTL, RTT)
            return true;
        }

        return false;
    }

    private function createPacket(): string
    {
        $type = 8; // Echo request
        $code = 0;
        $identifier = 1234; // Example identifier
        $sequence = 1; // Example sequence number
        $data = "PingHost";

        $packet = pack('CCnnn', $type, $code, 0, $identifier, $sequence) . $data;
        $checksum = $this->calculateChecksum($packet);
        return pack('CCnnn', $type, $code, $checksum, $identifier, $sequence) . $data;
    }


    private function calculateChecksum(string $data): int
    {
        $len = strlen($data);
        if ($len % 2 == 1) {
            $data .= "\x00"; // Pad with a null byte if odd length
        }
        $sum = 0;
        $words = unpack('n*', $data); // Unpack into 16-bit unsigned integers
        foreach ($words as $word) {
            $sum += $word;
        }
        while ($sum >> 16) {
            $sum = ($sum & 0xFFFF) + ($sum >> 16);
        }
        return ~$sum & 0xFFFF; // One's complement
    }
}
