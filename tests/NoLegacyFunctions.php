<?php
/*
 * NoLegacyFunctions.php
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

namespace LibreNMS\Tests;

use Illuminate\Support\Str;

class NoLegacyFunctions extends TestCase
{
    private $dbFacile = [
        'dbArrayToRaw',
        'dbBeginTransaction',
        'dbBulkInsert',
        'dbCommitTransaction',
        'dbConnect',
        'dbDelete',
        'dbDeleteOrphans',
        'dbFetch',
        'dbFetchCell',
        'dbFetchColumn',
        'dbFetchKeyValue',
        'dbFetchRow',
        'dbFetchRows',
        'dbGenPlaceholders',
        'dbHandleException',
        'dbInsert',
        'dbIsConnected',
        'dbPlaceHolders',
        'dbQuery',
        'dbRollbackTransaction',
        'dbSyncRelationship',
        'dbSyncRelationships',
        'dbUpdate',
        'recordDbStatistic',
    ];

    public function testNoLegacyDbFacileFunctionsAdded()
    {
        $diff = `git diff -w origin/master HEAD | grep -E "^\+"`;

        $regex = '/' . implode('|', array_map(function ($f) {
            return preg_quote($f) . '\s*(?=\()'; // try to make sure it is a function call
        }, $this->dbFacile)) . '/';

        $invalid_functions = [];

        if (preg_match($regex, $diff)) { // fast check to save time
            // split into files and
            foreach (explode('+++ ', $diff) as $section) {
                if (! str_contains($section, PHP_EOL)) {
                    continue;
                }

                $file = Str::after(substr($section, 0, strpos($section, PHP_EOL)), 'b/');
                unset($matches);
                preg_match_all($regex, $section, $matches);
                if (! empty($matches[0])) {
                    $invalid_functions[$file] = array_count_values($matches[0]);
                }
            }
        }

        $found = implode(PHP_EOL, array_map(function ($file) use ($invalid_functions) {
            return "\e[1m$file\e[0m: " . implode(', ', array_map(function ($function) use ($file, $invalid_functions) {
                return "$function ({$invalid_functions[$file][$function]})";
            }, array_keys($invalid_functions[$file])));
        }, array_keys($invalid_functions)));

        $this->assertEmpty($invalid_functions, <<<MESSAGE
\e[41;97;1mYour code changes use deprecated dbFacile function(s)\e[0m

$found

You must use Laravel Eloquent or Fluent to access the database.

See documentation:
https://laravel.com/docs/eloquent
https://laravel.com/docs/queries
MESSAGE
        );
    }
}
