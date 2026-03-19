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

namespace LibreNMS\Data\Source\Icmp;

use LibreNMS\Enum\FpingExitCode;
use LibreNMS\Exceptions\FpingUnparsableLine;

class FpingAliveResponse implements PingResultInterface
{
    /**
     * @param  FpingExitCode  $exit_code  Return code from fping
     * @param  string|null  $host  Hostname/IP pinged
     */
    public function __construct(
        public FpingExitCode $exit_code,
        public readonly ?string $host = null
    ) {
    }

    public static function parseLine(string $output): FpingAliveResponse
    {
        $matched = preg_match('/^(\S+) is (alive|unreachable)$/', $output, $parsed);
        if ($matched) {
            [, $host, $result] = array_pad($parsed, 3, 0);

            return new static(
                $result == 'alive' ? FpingExitCode::Success : FpingExitCode::Unreachable,
                $host,
            );
        }

        $matched = preg_match('/^(\S+): (Name or service not known|Temporary failure in name resolution)$/', $output, $parsed);
        if ($matched) {
            [, $host, $result] = array_pad($parsed, 3, 0);

            try {
                $ret_code = match ($result) {
                    'Name or service not known' => FpingExitCode::InvalidHost,
                    'Temporary failure in name resolution' => FpingExitCode::SysCallFail,
                };
            } catch (\UnhandledMatchError) {
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
    public function isAlive(): bool
    {
        return $this->exit_code === FpingExitCode::Success;
    }

    /**
     * Change the exit code to 0, this may be appropriate when a non-fatal error was encountered
     */
    public function ignoreFailure(): void
    {
        $this->exit_code = FpingExitCode::Success;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function getExitCode(): FpingExitCode
    {
        return $this->exit_code;
    }
}
