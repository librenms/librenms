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
use Illuminate\Support\Str;
use LibreNMS\Util\StringHelpers;
use Log;

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
            values: [],
            stderr: $stderr,
            exitCode: $exitCode,
            command: $command,
        );

        $this->raw = (string) preg_replace('/Wrong Type \(should be .*\): /', '', $output);
    }

    public function isValid(bool $ignore_partial = false): bool
    {
        if ($ignore_partial) {
            return ! empty($this->values());
        }

        $this->errorMessage = '';

        $invalid = (! empty($this->stderr) && preg_match('/(Timeout: No Response from .*|Unknown user name|Authentication failure|Error: OID not increasing: .*)/', $this->stderr, $errors))
            || empty($this->raw)
            || preg_match('/(No Such Instance|No Such Object|No more variables left).*/', $this->raw, $errors);

        if ($invalid) {
            $this->errorMessage = $errors[0] ?? 'Empty Output';
            Log::debug(sprintf('SNMP query failed. Exit Code: %s Empty: %s Bad String: %s', $this->exitCode, var_export(empty($this->raw), true), $errors[0] ?? 'not found'));

            return false;
        }

        return true;
    }

    public function values(): array
    {
        if (! empty($this->values)) {
            return $this->values;
        }

        $this->inferValueEncoding ??= ! StringHelpers::isValidUtf8($this->raw);
        $this->values = [];
        $line = strtok($this->raw, PHP_EOL);
        while ($line !== false) {
            if (Str::contains($line, ['at this OID', 'this MIB View', 'End of MIB']) || str_ends_with($line, ' = NULL')) {
                $line = strtok(PHP_EOL);
                continue;
            }

            $parts = explode(self::KEY_VALUE_DELIMITER, $line, 2);
            if (count($parts) == 1) {
                array_unshift($parts, '');
            }
            [$oid, $value] = $parts;

            $line = strtok(PHP_EOL);
            while ($line !== false && ! Str::contains($line, self::KEY_VALUE_DELIMITER)) {
                $value .= PHP_EOL . $line;
                $line = strtok(PHP_EOL);
            }

            if (LibrenmsConfig::get('snmp.unescape')) {
                $value = stripslashes($value);
            }

            if (Str::startsWith($value, '"') && Str::endsWith($value, '"')) {
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
