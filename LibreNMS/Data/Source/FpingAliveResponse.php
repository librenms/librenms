<?php

/*
 * FpingResponse.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2026 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.au>
 */

namespace LibreNMS\Data\Source;

use LibreNMS\Exceptions\FpingUnparsableLine;

class FpingAliveResponse
{
    const SUCCESS = 0;
    const UNREACHABLE = 1;
    const INVALID_HOST = 2;
    const INVALID_ARGS = 3;
    const SYS_CALL_FAIL = 4;

    /**
     * @param  int  $exit_code  Return code from fping
     * @param  string|null  $host  Hostname/IP pinged
     */
    private function __construct(
        public int $exit_code,
        public readonly ?string $host = null)
    {
    }

    public static function parseLine(string $output): FpingAliveResponse
    {
        $matched = preg_match('/^(\S+) is (alive|unreachable)$/', $output, $parsed);
        if ($matched) {
            [, $host, $result] = array_pad($parsed, 3, 0);
            return new static(
                ($result == 'alive' ? self::SUCCESS : self::UNREACHABLE),
                $host,
            );
        }

        $matched = preg_match('/^(\S+): (Name or service not known|Temporary failure in name resolution)$/', $output, $parsed);
        if ($matched) {
            [, $host, $result] = array_pad($parsed, 3, 0);

            try {
                $ret_code = match ($result) {
                   'Name or service not known' => self::INVALID_HOST,
                   'Temporary failure in name resolution' => self::SYS_CALL_FAIL,
                };
            } catch (\UnhandledMatchError $e) {
                throw new FpingUnparsableLine($output);
            }

            return new static(
                $ret_code,
                $host,
            );
        }

        throw new FpingUnparsableLine($output);
    }
    /**
     * Ping result was successful.
     * fping didn't have an error and we got at least one ICMP packet back.
     */
    public function success(): bool
    {
        return $this->exit_code == self::SUCCESS;
    }
}
