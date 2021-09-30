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

class SnmpResponse
{
    private $raw;

    public function __construct(string $output, int $exitCode = 0)
    {
        $this->exitCode = $exitCode;
        $output = str_replace('Wrong Type (should be OBJECT IDENTIFIER): ', '', $output);
        $this->raw = trim($output);
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
        return Arr::first($this->values());
    }

    public function values(): array
    {
        $values = [];
        foreach (explode(PHP_EOL, $this->raw) as $line) {
            [$key, $value] = explode(' ', $line, 2);
            $values[trim($key)] = $value;
        }

        return $values;
    }
}
