<?php

/**
 * DbFacileTest.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Tests\Unit;

use Illuminate\Support\Facades\DB;
use LibreNMS\Tests\TestCase;

final class DbFacileTest extends TestCase
{
    public function testPlaceHoldersAllowsNow(): void
    {
        $values = ['name' => 'test', 'updated' => ['NOW()']];

        $this->assertSame([':name', 'NOW()'], dbPlaceHolders($values));
        $this->assertSame(['name' => 'test'], $values);
    }

    public function testPlaceHoldersDoesNotSpliceArbitraryArrays(): void
    {
        $values = [
            'name' => ['(SELECT password FROM users LIMIT 1)'],
            'nested' => ['a' => ['b' => 'c']],
            'multi' => ['NOW()', 'extra'],
            'lower' => ['now()'],
        ];

        $this->assertSame([':name', ':nested', ':multi', ':lower'], dbPlaceHolders($values));
        $this->assertSame(['name' => null, 'nested' => null, 'multi' => null, 'lower' => null], $values);
    }

    public function testRawSqlExpression(): void
    {
        $this->assertSame('NOW()', dbRawSqlExpression(['NOW()']));
        $this->assertNull(dbRawSqlExpression(['UNIX_TIMESTAMP()']));
        $this->assertNull(dbRawSqlExpression(['1) OR (1=1']));
        $this->assertNull(dbRawSqlExpression([]));
        $this->assertNull(dbRawSqlExpression([['NOW()']]));
    }

    public function testUpdateAllowsNow(): void
    {
        $queries = DB::pretend(function (): void {
            dbUpdate(['value' => 'x', 'updated' => ['NOW()']], 'table', '`id` = ?', [5]);
        });

        $this->assertCount(1, $queries);
        $this->assertSame("UPDATE `table` set `value`='x',`updated`=NOW() WHERE `id` = 5", $queries[0]['query']);
    }

    public function testUpdateDoesNotSpliceArbitraryArrays(): void
    {
        $queries = DB::pretend(function (): void {
            dbUpdate([
                'state' => ['(SELECT password FROM users LIMIT 1)'],
                'status' => 'up',
                'error' => ['x' => ['y' => 'z']],
            ], 'table', '`id` = ?', [5]);
        });

        $this->assertCount(1, $queries);
        $this->assertSame("UPDATE `table` set `state`=null,`status`='up',`error`=null WHERE `id` = 5", $queries[0]['query']);
    }
}
