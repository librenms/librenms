<?php
/**
 * AlertDB.php
 *
 * Extending the built in logging to add an event logger function
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
 *
 * Original code:
 * @author Daniel Preussker <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 *
 * Modified by:
 * @link       https://www.librenms.org
 * @copyright  2019 KanREN, Inc.
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Alert;

use LibreNMS\Alerting\QueryBuilderParser;

class AlertDB
{
    /**
     * @param string $rule
     * @param mixed $query_builder
     * @return bool|string
     */
    public static function genSQL($rule, $query_builder = false)
    {
        if ($query_builder) {
            return QueryBuilderParser::fromJson($query_builder)->toSql();
        } else {
            return self::genSQLOld($rule);
        }
    }

    /**
     * Generate SQL from Rule
     * @param string $rule Rule to generate SQL for
     * @return string|bool
     */
    public static function genSQLOld($rule)
    {
        $rule = AlertUtil::runMacros($rule);
        if (empty($rule)) {
            //Cannot resolve Macros due to recursion. Rule is invalid.
            return false;
        }
        //Pretty-print rule to dissect easier
        $pretty = ['&&' => ' && ', '||' => ' || '];
        $rule = str_replace(array_keys($pretty), $pretty, $rule);
        $tmp = explode(' ', $rule);
        $tables = [];
        foreach ($tmp as $opt) {
            if (strstr($opt, '%') && strstr($opt, '.')) {
                $tmpp = explode('.', $opt, 2);
                $tmpp[0] = str_replace('%', '', $tmpp[0]);
                $tables[] = str_replace('(', '', $tmpp[0]);
                $rule = str_replace($opt, $tmpp[0] . '.' . $tmpp[1], $rule);
            }
        }
        $tables = array_keys(array_flip($tables));
        if (dbFetchCell('SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_NAME = ? && COLUMN_NAME = ?', [$tables[0], 'device_id']) != 1) {
            //Our first table has no valid glue, append the 'devices' table to it!
            array_unshift($tables, 'devices');
        }
        $x = sizeof($tables) - 1;
        $i = 0;
        $join = '';
        while ($i < $x) {
            if (isset($tables[$i + 1])) {
                $gtmp = ResolveGlues([$tables[$i + 1]], 'device_id');
                if ($gtmp === false) {
                    //Cannot resolve glue-chain. Rule is invalid.
                    return false;
                }
                $last = '';
                $qry = '';
                foreach ($gtmp as $glue) {
                    if (empty($last)) {
                        [$tmp,$last] = explode('.', $glue);
                        $qry .= $glue . ' = ';
                    } else {
                        [$tmp,$new] = explode('.', $glue);
                        $qry .= $tmp . '.' . $last . ' && ' . $tmp . '.' . $new . ' = ';
                        $last = $new;
                    }
                    if (! in_array($tmp, $tables)) {
                        $tables[] = $tmp;
                    }
                }
                $join .= '( ' . $qry . $tables[0] . '.device_id ) && ';
            }
            $i++;
        }
        $sql = 'SELECT * FROM ' . implode(',', $tables) . ' WHERE (' . $join . '' . str_replace('(', '', $tables[0]) . '.device_id = ?) && (' . str_replace(['%', '@', '!~', '~'], ['', '.*', 'NOT REGEXP', 'REGEXP'], $rule) . ')';

        return $sql;
    }
}
