<?php
/**
 * SnmpResponse.php
 *
 * Responsible for parsing net-snmp output into usable PHP data structures.
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Source;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LibreNMS\Config;
use Log;

class SnmpResponse
{
    protected const DELIMITER = ' = ';
    /**
     * @var string
     */
    private $raw;
    /**
     * @var int
     */
    private $exitCode;
    /**
     * @var string
     */
    private $errorMessage;
    /**
     * @var string
     */
    private $stderr;

    /**
     * Create a new response object filling with output from the net-snmp command.
     *
     * @param  string  $output
     * @param  string  $errorOutput
     * @param  int  $exitCode
     */
    public function __construct(string $output, string $errorOutput = '', int $exitCode = 0)
    {
        $this->exitCode = $exitCode;
        $this->raw = preg_replace('/Wrong Type \(should be .*\): /', '', $output);
        $this->stderr = $errorOutput;
    }

    public function isValid(): bool
    {
        $this->errorMessage = '';
        // not checking exitCode because I think it may lead to false negatives
        $invalid = preg_match('/(Timeout: No Response from .*|Unknown user name|Authentication failure|Error: OID not increasing: .*)/', $this->stderr, $errors)
            || empty($this->raw)
            || preg_match('/(No Such Instance|No Such Object|No more variables left).*/', $this->raw, $errors);

        if ($invalid) {
            $this->errorMessage = $errors[0] ?? 'Empty Output';
            Log::debug(sprintf('SNMP query failed. Exit Code: %s Empty: %s Bad String: %s', $this->exitCode, var_export(empty($this->raw), true), $errors[0] ?? 'not found'));

            return false;
        }

        return true;
    }

    /**
     * Get the error message if any
     */
    public function getErrorMessage(): string
    {
        if (empty($this->errorMessage)) {
            $this->isValid(); // if no error message, double check.
        }

        return $this->errorMessage;
    }

    /**
     * Get the raw output returned by net-snmp
     */
    public function raw(): string
    {
        return $this->raw;
    }

    public function value(): string
    {
        return Arr::first($this->values(), null, '');
    }

    public function values(): array
    {
        $values = [];
        $line = strtok($this->raw, PHP_EOL);
        while ($line !== false) {
            if (Str::contains($line, ['at this OID', 'this MIB View', 'End of MIB'])) {
                // these occur when we seek past the end of data, usually the end of the response, but grab the next line and continue
                $line = strtok(PHP_EOL);
                continue;
            }

            $parts = explode(self::DELIMITER, $line, 2);
            if (count($parts) == 1) {
                array_unshift($parts, '');
            }
            [$oid, $value] = $parts;

            $line = strtok(PHP_EOL); // get the next line and concatenate multi-line values
            while ($line !== false && ! Str::contains($line, self::DELIMITER)) {
                $value .= PHP_EOL . $line;
                $line = strtok(PHP_EOL);
            }

            // remove extra escapes
            if (Config::get('snmp.unescape')) {
                $value = stripslashes($value);
            }

            if (Str::startsWith($value, '"') && Str::endsWith($value, '"')) {
                // unformatted string from net-snmp, remove extra escapes
                $values[$oid] = trim(stripslashes($value), "\" \n\r");
            } else {
                $values[$oid] = trim($value);
            }
        }

        return $values;
    }

    public function valuesByIndex(array &$array = []): array
    {
        foreach ($this->values() as $oid => $value) {
            [$name, $index] = array_pad(explode('.', $oid, 2), 2, '');
            $array[$index][$name] = $value;
        }

        return $array;
    }

    public function table(int $group = 0, array &$array = []): array
    {
        foreach ($this->values() as $key => $value) {
            preg_match_all('/([^[\]]+)/', $key, $parts);
            $parts = $parts[1];
            array_splice($parts, $group, 0, array_shift($parts)); // move the oid name to the correct depth

            // merge the parts into an array, creating keys if they don't exist
            $tmp = &$array;
            foreach ($parts as $part) {
                $key = trim($part, '"');
                $tmp = &$tmp[$key];
            }
            $tmp = $value; // assign the value as the leaf
        }

        return Arr::wrap($array); // if no parts, wrap the value
    }

    /**
     * Map an snmp table with callback. If invalid data is encountered, an empty collection is returned.
     * Variables passed to the callback will be an array of row values followed by each individual index.
     */
    public function mapTable(callable $callback): Collection
    {
        if (! $this->isValid()) {
            return new Collection;
        }

        return collect($this->values())
            ->map(function ($value, $oid) {
                $parts = explode('[', rtrim($oid, ']'), 2);

                return [
                    '_index' => $parts[1] ?? '',
                    $parts[0] => $value,
                ];
            })
            ->groupBy('_index')
            ->map(function ($values, $index) use ($callback) {
                $values = array_merge(...$values);
                unset($values['_index']);

                return call_user_func($callback, $values, ...explode('][', (string) $index));
            });
    }

    /**
     * @return int
     */
    public function getExitCode(): int
    {
        return $this->exitCode;
    }
}
