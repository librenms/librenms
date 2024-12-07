<?php
/*
 * CheckDatabaseTableNamesCase.php
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

use Illuminate\Support\Facades\DB;
use LibreNMS\DB\Eloquent;
use LibreNMS\Interfaces\Validation;
use LibreNMS\ValidationResult;

class CheckDatabaseTableNamesCase implements Validation
{
    /**
     * @inheritDoc
     */
    public function validate(): ValidationResult
    {
        // Test for lower case table name support
        $lc_mode = DB::selectOne('SELECT @@global.lower_case_table_names as mode')->mode;
        if ($lc_mode != 0) {
            ValidationResult::fail(
                trans('validation.validations.database.CheckDatabaseTableNamesCase.fail'),
                trans('validation.validations.database.CheckDatabaseTableNamesCase.fix')
            );
        }

        return ValidationResult::ok(trans('validation.validations.database.CheckDatabaseTableNamesCase.ok'));
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return Eloquent::isConnected();
    }
}
