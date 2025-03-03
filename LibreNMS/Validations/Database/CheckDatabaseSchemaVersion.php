<?php
/*
 * CheckSchemaVersion.php
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

use LibreNMS\DB\Eloquent;
use LibreNMS\DB\Schema;
use LibreNMS\Interfaces\Validation;
use LibreNMS\Interfaces\ValidationFixer;
use LibreNMS\ValidationResult;

class CheckDatabaseSchemaVersion implements Validation, ValidationFixer
{
    /** @var bool|null */
    private static $current = null;

    /**
     * @inheritDoc
     */
    public function validate(): ValidationResult
    {
        self::$current = false;

        if (! Schema::isCurrent()) {
            return ValidationResult::fail(trans('validation.validations.database.CheckSchemaVersion.fail_outdated'), './lnms migrate')
                ->setFixer(__CLASS__);
        }

        $migrations = Schema::getUnexpectedMigrations();
        if ($migrations->isNotEmpty()) {
            return ValidationResult::warn(trans('validation.validations.database.CheckSchemaVersion.warn_extra_migrations', ['migrations' => $migrations->implode(', ')]));
        }

        self::$current = true;

        return ValidationResult::ok(trans('validation.validations.database.CheckSchemaVersion.ok'));
    }

    public static function isCurrent(): bool
    {
        if (self::$current === null) {
            (new static)->validate();
        }

        return self::$current;
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return Eloquent::isConnected();
    }

    public function fix(): bool
    {
        return \Artisan::call('migrate', ['--force' => true, '--isolated' => true]) === 0;
    }
}
