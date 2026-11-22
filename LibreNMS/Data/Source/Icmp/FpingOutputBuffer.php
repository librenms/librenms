<?php

/**
 * FpingOutputBuffer.php
 *
 * Parse bulk fping output that arrives in arbitrary chunks.
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Source\Icmp;

use LibreNMS\Exceptions\FpingUnparsableLine;

class FpingOutputBuffer
{
    /** @var array<string, string> incomplete last line per output stream */
    private array $partials = [];

    /**
     * Add output and parse the complete lines in it.
     *
     * @param  string  $stream  the output stream, lines are not mixed between streams
     * @return FpingResponse[]
     */
    public function push(string $stream, string $output): array
    {
        $lines = explode(PHP_EOL, ($this->partials[$stream] ?? '') . $output);
        $this->partials[$stream] = array_pop($lines);

        return $this->parse($lines);
    }

    /**
     * Parse the remaining incomplete lines, call this once the output has ended.
     *
     * @return FpingResponse[]
     */
    public function flush(): array
    {
        $lines = $this->partials;
        $this->partials = [];

        return $this->parse($lines);
    }

    /**
     * @param  string[]  $lines
     * @return FpingResponse[]
     */
    private function parse(array $lines): array
    {
        $responses = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            try {
                $responses[] = FpingResponse::parseAliveLine($line);
            } catch (FpingUnparsableLine) {
                // ignore other messages, such as ICMP unreachable notices
            }
        }

        return $responses;
    }
}
