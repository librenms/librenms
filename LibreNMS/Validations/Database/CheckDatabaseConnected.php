<?php
/**
 * CheckDatabaseConnected.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2024 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Validations\Database;

use Illuminate\Support\Facades\DB;
use LibreNMS\Interfaces\Validation;
use LibreNMS\ValidationResult;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class CheckDatabaseConnected implements Validation
{
    /**
     * @inheritDoc
     */
    public function validate(): ValidationResult
    {
        try {
            if (DB::connection()->getPdo()) {
                DB::select('select 1 from migrations limit 1'); // test query

                return ValidationResult::ok(trans('validation.validations.database.CheckDatabaseConnected.ok'));
            }

            return ValidationResult::fail(trans('validation.validations.database.CheckDatabaseConnected.fail')); // probably not possible to hit this failure
        } catch (\PDOException $e) {
            // handle specific mysql/mariadb codes
            if ($e->getCode() == 2002 || $e->getCode() == 2003) {
                $failure = ValidationResult::fail(trans('validation.validations.database.CheckDatabaseConnected.fail_connect'), $this->tryToGenerateFix());
                $failure->setList('Error:', [$e->getMessage()]);

                return $failure;
            }

            if ($e->getCode() == 1044) {
                $db = config('database.default');
                $username = config('database.connections.database.' . $db . '.username');
                $db_name = config('database.connections.database.' . $db . '.database');
                $failure = ValidationResult::fail(
                    trans('validation.validations.database.CheckDatabaseConnected.fail_access'),
                    "GRANT ALL PRIVILEGES ON $db_name.* TO '$username'@'localhost';",
                );
                $failure->setList('Error:', [$e->getMessage()]);

                return $failure;
            }

            if ($e->getCode() == 1045) {
                $failure = ValidationResult::fail(trans('validation.validations.database.CheckDatabaseConnected.fail_auth', ['env_file' => base_path('.env')]));
                $failure->setList('Error:', [$e->getMessage()]);

                return $failure;
            }

            return ValidationResult::fail($e->getMessage()); // all other errors
        }
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return true;
    }

    private function tryToGenerateFix(): ?string
    {
        $host = config('database.connections.database.' . config('database.default') . '.host');
        $fix = null;
        if (empty($host) || $host == 'localhost' || str_starts_with($host, '127.')) {
            $finder = new ExecutableFinder;
            $systemctl = $finder->find('systemctl');
            if ($systemctl) {
                $units = new Process([$systemctl, 'list-unit-files']);
                $units->run();
                preg_match('/(mariadb|mysqld)\.service\s+(\w+)\s+(\w+)/', $units->getOutput(), $matches);
                $unit = $matches[1] ?? 'mysqld';
                if (isset($matches[2]) && $matches[2] == 'disabled') {
                    return "systemctl enable --now $unit.service";
                }

                return "systemctl restart $unit.service";
            }

            $service = $finder->find('service');
            if ($service) {
                return 'service start mysqld'; // probably correct :D
            }
        }

        return null;
    }
}
