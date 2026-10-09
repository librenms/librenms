<?php

/**
 * RawSnmpResponse.php
 *
 * SnmpResponse subclass built from raw net-snmp command line output.
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

namespace LibreNMS\Data\Source\Snmp;

use App\Facades\LibrenmsConfig;
use LibreNMS\Util\StringHelpers;

class RawSnmpResponse extends SnmpResponse
{
    protected readonly string $raw;
    private ?bool $inferValueEncoding = null;

    /**
     * Create a new raw response object filled with output from the net-snmp command.
     *
     * @param  array<int, string>  $command
     */
    public function __construct(
        string $output = '',
        string $stderr = '',
        int $exitCode = 0,
        array $command = [],
    ) {
        parent::__construct(
            rawValues: [],
            stderr: $stderr,
            exitCode: $exitCode,
            command: $command,
        );

        $this->raw = (string) preg_replace('/Wrong Type \(should be .*\): /', '', $output);
    }

    protected function findBadString(): ?string
    {
        if (preg_match(self::BAD_STRING_REGEX, $this->raw, $errors)) {
            return $errors[0];
        }

        return null;
    }

    public function isEmpty(): bool
    {
        return empty($this->raw);
    }

    /**
     * @return array<string, mixed>
     */
    public function values(): array
    {
        if (! empty($this->values)) {
            return $this->values;
        }

        $this->inferValueEncoding ??= ! StringHelpers::isValidUtf8($this->raw);
        $this->values = [];
        $line = strtok($this->raw, PHP_EOL);
        while ($line !== false) {
            if (preg_match(self::BAD_VALUE_REGEX, $line)) {
                $line = strtok(PHP_EOL);
                continue;
            }

            $parts = explode(self::KEY_VALUE_DELIMITER, $line, 2);
            if (count($parts) == 1) {
                array_unshift($parts, '');
            }
            [$oid, $value] = $parts;

            $line = strtok(PHP_EOL);
            while ($line !== false && ! str_contains($line, self::KEY_VALUE_DELIMITER)) {
                $value .= PHP_EOL . $line;
                $line = strtok(PHP_EOL);
            }

            if (LibrenmsConfig::get('snmp.unescape')) {
                $value = stripslashes($value);
            }

            if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
                $value = trim(stripslashes($value), "\" \n\r");
            } else {
                $value = trim($value);
            }

            $this->values[$oid] = $this->inferValueEncoding
                ? StringHelpers::inferEncoding($value)
                : $value;
        }

        return $this->values;
    }

    public function raw(): string
    {
        return $this->raw;
    }

    public function append(SnmpResponse $response): SnmpResponse
    {
        if ($response instanceof RawSnmpResponse) {
            $newResponse = new RawSnmpResponse(
                $this->raw . $response->raw,
                $this->stderr . $response->stderr,
                $this->exitCode ?: $response->exitCode,
                $response->command ?: $this->command,
            );
        } else {
            $newResponse = new SnmpResponse(
                array_merge($this->values(), $response->values()),
                $this->stderr . $response->stderr,
                $this->exitCode ?: $response->exitCode,
                $response->command ?: $this->command,
            );
        }

        $newResponse->errorMessage = $this->errorMessage ?: $response->errorMessage;

        return $newResponse;
    }

    public function __sleep()
    {
        return ['raw', 'exitCode', 'stderr', 'command', 'values', 'errorMessage'];
    }
}
