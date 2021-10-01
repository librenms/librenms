<?php
/*
 * SnmpResponse.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Source;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Log;

class SnmpResponse
{
    const DELIMITER = ' = ';
    /**
     * @var string
     */
    private $raw;
    /**
     * @var int
     */
    private $exitCode;

    public function __construct(string $output, int $exitCode = 0)
    {
        $this->exitCode = $exitCode;
        $this->raw = preg_replace('/Wrong Type \(should be .*\): /', '', $output);
    }

    public function isValid(): bool
    {
        // not checking exitCode because I think it may lead to false negatives
        $invalid = empty($this->raw)
            || preg_match('/(No Such Instance|No Such Object|No more variables left|Authentication failure|Timeout: No Response from)/', $this->raw, $matches);

        if ($invalid) {
            Log::debug(sprintf('SNMP query failed. Exit Code: %s Empty: %s Bad String: %s', $this->exitCode, var_export(empty($this->raw), true), $matches[1] ?? 'not found'));

            return false;
        }

        return true;
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
            if (Str::contains($line, ['at this OID', 'this MIB View'])) {
                // these occur when we seek past the end of data, usually the end of the response, but grab the next line and continue
                $line = strtok(PHP_EOL);
                continue;
            }

            [$oid, $value] = explode(self::DELIMITER, $line, 2);

            $line = strtok(PHP_EOL); // get the next line and concatenate multi-line values
            while ($line !== false && ! Str::contains($line, self::DELIMITER)) {
                $value .= PHP_EOL . $line;
                $line = strtok(PHP_EOL);
            }

            $values[$oid] = $value;
        }

        return $values;
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

        return $array;
    }

    /**
     * @return int
     */
    public function getExitCode(): int
    {
        return $this->exitCode;
    }
}
