<?php
/**
 * Log.php
 *
 * check_log
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Services\Checks;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LibreNMS\Services\CheckParameter;
use LibreNMS\Services\DefaultServiceCheck;

class Log extends DefaultServiceCheck
{
    public function availableParameters(): Collection
    {
        return collect([
            CheckParameter::make('--filename', '-F', 'logfile', 'Log file to check'),
            CheckParameter::make('--oldlog', '-O', 'oldlog', 'Location to store the old log file, must start with /tmp/check_log/'),
            CheckParameter::make('--query', '-q', 'query', 'grep query to run against the file'),
        ]);
    }

    public function validateParameters(): array
    {
        return [
            '--filename' => function ($attribute, $value, $fail) {
                if (Str::startsWith($value, ['/etc', '/usr', '/bin']) || ! is_readable($value)) {
                    $fail(trans('validation.exists', ['attribute' => $attribute]));
                }
            },
            '--oldlog' => 'required|starts_with:/tmp/check_log/',
            '--query' => 'required|string',
        ];
    }
}
