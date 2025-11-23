<?php
/**
 * NtpRequestMessage.php
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

class NtpRequestMessage implements UdpRequestMessage
{
    public function getPayload(): string
    {
        // NTP packet format (48 bytes)
        // LI = 0, VN = 3, Mode = 3 (client)
        $packet = chr(0x1b); // 00011011 in binary
        $packet .= str_repeat(chr(0), 47);
        return $packet;
    }

    public function validateResponse(string $payload): bool
    {
        // NTP response should be 48 bytes
        // and have mode = 4 (server) or mode = 5 (broadcast)
        if (strlen($payload) < 48) {
            return false;
        }

        $firstByte = ord($payload[0]);
        $mode = $firstByte & 0x07;

        // Valid modes: 4 (server) or 5 (broadcast)
        return $mode === 4 || $mode === 5;
    }
}
