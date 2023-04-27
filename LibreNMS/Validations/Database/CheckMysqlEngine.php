<?php
/*
 * CheckMysqlEngine.php
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Validations\Database;

use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LibreNMS\DB\Eloquent;
use LibreNMS\Interfaces\Validation;
use LibreNMS\Interfaces\ValidationFixer;
use LibreNMS\ValidationResult;

class CheckMysqlEngine implements Validation, ValidationFixer
{
    /**
     * @inheritDoc
     */
    public function validate(): ValidationResult
    {
        $tables = $this->findNonInnodbTables();

        if ($tables->isNotEmpty()) {
            return ValidationResult::warn(trans('validation.validations.database.CheckMysqlEngine.fail'))
                ->setFixer(__CLASS__)
                ->setList(trans('validation.validations.database.CheckMysqlEngine.tables'), $tables->all());
        }

        return ValidationResult::ok(trans('validation.validations.database.CheckMysqlEngine.ok'));
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return Eloquent::isConnected();
    }

    /**
     * @inheritDoc
     */
    public function fix(): bool
    {
        try {
            $db = $this->databaseName();
            $tables = $this->findNonInnodbTables();

            foreach ($tables as $table) {
                DB::statement("ALTER TABLE $db.$table ENGINE=InnoDB;");
            }
        } catch (QueryException $e) {
            return false;
        }

        return true;
    }

    private function databaseName(): string
    {
        return \config('database.connections.' . \config('database.default') . '.database');
    }

    private function findNonInnodbTables(): Collection
    {
        $db = $this->databaseName();

        return DB::table('information_schema.tables')
            ->where('TABLE_SCHEMA', $db)
            ->where('ENGINE', '!=', 'InnoDB')
            ->pluck('TABLE_NAME');
    }
}
