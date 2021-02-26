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

    public function testNoLegacyFunctionsAdded()
    {
        $diff = `git diff -w origin/master HEAD | grep -E "^\+"`;

        $regex = '/' . implode('|', array_map(function ($f) {
                return $f . '\s*\(';
            }, $this->dbFacile)) . '/';

        $invalid_functions = [];
        $file = 'unknown';
        if (preg_match($regex, $diff)) { // fast check to save time
            // split into files and
            foreach (explode('+++ ', $diff) as $section) {
                if (! str_contains($section, PHP_EOL)) {
                    continue;
                }

                $file = substr($section, 0, strpos($section, PHP_EOL));
                unset($matches);
                preg_match_all($regex, $section, $matches);
                if (! empty($matches[0])) {
                    dd($section, $file, $matches);
                }
            }

            dd($invalid_functions);

            foreach ($this->dbFacile as $function) {
                if (Str::contains($diff, $function . '(')) {
                    $invalid_functions[$function] = substr_count($regex,);
                }
            }
        }

        if (! empty($invalid_functions)) {
            $functions = implode(',', $invalid_functions);
            $this->fail(<<<MESSAGE
\e[41;97;1mYour code changes contain use of deprecated dbFacile function(s): $functions\e[0m
You must use Laravel Eloquent or Fluent to access the database.
https://laravel.com/docs/eloquent
https://laravel.com/docs/queries
MESSAGE
            );

        }
    }
}
