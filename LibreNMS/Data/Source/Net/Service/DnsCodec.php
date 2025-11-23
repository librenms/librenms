<?php
/**
 * DnsCodec.php
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

class DnsCodec implements UdpCodec
{
    private $domain;
    private $transactionId;

    public function __construct(string $domain = 'localhost')
    {
        $this->domain = $domain;
        $this->transactionId = random_int(0, 65535);
    }

    public function getPayload(): string
    {
        // Simplified DNS query packet
        $packet = pack('n', $this->transactionId); // Transaction ID
        $packet .= pack('n', 0x0100); // Flags: standard query
        $packet .= pack('n', 1); // Questions: 1
        $packet .= pack('n', 0); // Answer RRs: 0
        $packet .= pack('n', 0); // Authority RRs: 0
        $packet .= pack('n', 0); // Additional RRs: 0

        // Query name
        $parts = explode('.', $this->domain);
        foreach ($parts as $part) {
            $packet .= chr(strlen($part)) . $part;
        }
        $packet .= chr(0); // End of name

        $packet .= pack('n', 1); // Type: A
        $packet .= pack('n', 1); // Class: IN

        return $packet;
    }

    public function validateResponse(string $payload): bool
    {
        // Minimum DNS response size
        if (strlen($payload) < 12) {
            return false;
        }

        // Check transaction ID matches
        $responseId = unpack('n', substr($payload, 0, 2))[1];
        if ($responseId !== $this->transactionId) {
            return false;
        }

        // Check QR bit (should be 1 for response)
        $flags = unpack('n', substr($payload, 2, 2))[1];
        return ($flags & 0x8000) !== 0;
    }
}
