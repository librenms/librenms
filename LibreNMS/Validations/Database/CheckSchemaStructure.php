<?php

/**
 * CheckSchemaStructure.php
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
 * @link       http://librenms.org
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Validations\Database;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use LibreNMS\DB\Eloquent;
use LibreNMS\DB\Schema;
use LibreNMS\Interfaces\Validation;
use LibreNMS\Interfaces\ValidationFixer;
use LibreNMS\ValidationResult;
use Symfony\Component\Yaml\Yaml;

class CheckSchemaStructure implements Validation, ValidationFixer
{
    private array $descriptions = [];
    private array $schema_update = [];
    private readonly string $schema_file;
    private Schema $schemaManager;

    public function __construct(?string $schema_file = null)
    {
        $this->schema_file = $schema_file ?? resource_path('definitions/schema/db_schema.yaml');
        $this->schemaManager = new Schema(DB::connection());
    }

    public function validate(): ValidationResult
    {
        if (! is_file($this->schema_file)) {
            return ValidationResult::warn("We haven't detected the db_schema.yaml file");
        }

        $this->checkSchema();
        if (empty($this->schema_update)) {
            return ValidationResult::ok('Database schema correct');
        }

        return ValidationResult::fail("We have detected that your database schema may be wrong\n" . implode("\n", $this->descriptions))
            ->setFix('Run the following SQL statements to fix it')
            ->setFixer(self::class)
            ->setList('SQL Statements', $this->schema_update);
    }

    public function fix(): bool
    {
        try {
            $this->checkSchema();
            foreach ($this->schema_update as $query) {
                DB::statement($query);
            }
        } catch (QueryException) {
            return false;
        }

        return true;
    }

    public function enabled(): bool
    {
        return Eloquent::isConnected() && CheckDatabaseSchemaVersion::isCurrent();
    }

    private function checkSchema(): void
    {
        $master = (array) Yaml::parse(file_get_contents($this->schema_file));
        $changes = $this->schemaManager->compare($master);

        $this->descriptions = array_column($changes, 'description');
        $this->schema_update = [];
        foreach (array_column($changes, 'sql') as $sql) {
            is_array($sql) ? $this->schema_update = [...$this->schema_update, ...$sql] : $this->schema_update[] = $sql;
        }
    }
}
