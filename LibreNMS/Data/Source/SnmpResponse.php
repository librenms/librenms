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
use LibreNMS\Util\Oid;
use Log;

class SnmpResponse
{
    protected const DELIMITER = ' = ';

    public readonly string $raw;
    public readonly int $exitCode;
    public readonly string $stderr;

    private ?string $errorMessage = null;
    private ?array $values = null;

    /**
     * Create a new response object filling with output from the net-snmp command.
     *
     * @param  string  $output
     * @param  string  $errorOutput
     * @param  int  $exitCode
     */
    public function __construct(string $output, string $errorOutput = '', int $exitCode = 0)
    {
        $this->raw = (string) preg_replace('/Wrong Type \(should be .*\): /', '', $output);
        $this->stderr = $errorOutput;
        $this->exitCode = $exitCode;
    }

    public function isValid(bool $ignore_partial = false): bool
    {
        $this->errorMessage = '';
        $raw = $ignore_partial ? $this->getRawWithoutBadLines() : $this->raw;

        // not checking exitCode because I think it may lead to false negatives
        $invalid = preg_match('/(Timeout: No Response from .*|Unknown user name|Authentication failure|Error: OID not increasing: .*)/', $this->stderr, $errors)
            || empty($raw)
            || preg_match('/(No Such Instance|No Such Object|No more variables left).*/', $raw, $errors);

        if ($invalid) {
            $this->errorMessage = $errors[0] ?? 'Empty Output';
            Log::debug(sprintf('SNMP query failed. Exit Code: %s Empty: %s Bad String: %s', $this->exitCode, var_export(empty($raw), true), $errors[0] ?? 'not found'));

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
     * Gets the first value of this response.
     * If an oid or list of oids is given, return the first one found.
     * If forceNumeric is set, force the search to use numeric oids even if textual oids are given
     *
     * @throws \LibreNMS\Exceptions\InvalidOidException
     */
    public function value(array|string $oids = [], bool $forceNumeric = false): string
    {
        $values = $this->values();

        if (empty($oids)) {
            return Arr::first($values, null, '');
        }

        foreach (Arr::wrap($oids) as $oid) {
            if ($forceNumeric) {
                // translate all to numeric to make it easier to match
                $oid = Oid::toNumeric($oid);
            }

            if (! empty($values[$oid])) {
                return $values[$oid];
            }
        }

        return '';
    }

    public function values(): array
    {
        if (isset($this->values)) {
            return $this->values;
        }

        $this->values = [];
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
                $this->values[$oid] = trim(stripslashes($value), "\" \n\r");
            } else {
                $this->values[$oid] = trim($value);
            }
        }

        return $this->values;
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
            if (Str::contains($key, '[')) {
                // table
                preg_match_all('/([^[\]]+)/', $key, $parts);
                $parts = $parts[1]; // get all group 1 matches
            } else {
                // regular oid
                $parts = explode('.', $key);
            }

            // move the oid name to the correct depth
            array_splice($parts, $group, 0, array_shift($parts));

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
        if (! $this->isValid(true)) {
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

    /**
     * Filter bad lines from the raw output, examples:
     * "No Such Instance currently exists at this OID"
     * "No more variables left in this MIB View (It is past the end of the MIB tree)"
     */
    public function getRawWithoutBadLines(): string
    {
        return (string) preg_replace([
            '/^.*No Such Instance currently exists.*$/m',
            '/\n[^\r\n]+No more variables left[^\r\n]+$/s',
        ], '', $this->raw);
    }

    public function append(SnmpResponse $response): SnmpResponse
    {
        $newResponse = new static(
            $this->raw . $response->raw,
            $this->stderr . $response->stderr,
            $this->exitCode ?: $response->exitCode,
        );

        $newResponse->errorMessage = $this->errorMessage ?: $response->errorMessage;

        return $newResponse;
    }

    public function __toString(): string
    {
        return $this->raw;
    }
}
