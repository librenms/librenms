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

namespace LibreNMS\Polling;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SnmpResponse
{
    private $raw;

    public function __construct(string $output, int $exitCode = 0)
    {
        $this->exitCode = $exitCode;
        $output = preg_replace('/Wrong Type \(should be .*\): /', '', $output);
        $this->raw = rtrim($output, "\r\n");
    }

    public function isValid(): bool
    {
        return ! ($this->exitCode !== 0
            || empty($this->raw)
            || preg_match('/(No Such Instance|No Such Object|No more variables left|Authentication failure)/i', $this->raw));
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
        foreach (explode(PHP_EOL, $this->raw) as $line) {
            if (empty($line) || Str::contains($line, ['at this OID', 'this MIB View'])) {
                continue;
            }

            $split = explode(' ', $line, 2);
            if (count($split) == 2) {
                $values[$split[0]] = $split[1];
            } else {
                $values[] = $split[0];
            }
        }

        return $values;
    }

    public function table($group = 0, &$array = []): array
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
}
