<?php
/**
 * EnvTest.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit\Util;

use LibreNMS\Tests\TestCase;
use LibreNMS\Util\EnvHelper;

class EnvTest extends TestCase
{
    public function testParseArray()
    {
        putenv('PARSETEST=one,two');
        $this->assertSame(['one', 'two'], EnvHelper::parseArray('PARSETEST'), 'Could not parse simple array');
        $this->assertSame(['default'], EnvHelper::parseArray('PARSETESTNOTSET', 'default'), 'Did not get default value as expected');
        $this->assertSame(null, EnvHelper::parseArray('PARSETESTNOTSET'), 'Did not get null as expected when env not set');
        $this->assertSame(3, EnvHelper::parseArray('PARSETESTNOTSET', 3), 'Did not get default value (non-array) as expected');
        $this->assertSame('default', EnvHelper::parseArray('PARSETESTNOTSET', 'default', ['default']), 'Did not get default value as expected, excluding it from exploding');

        putenv('PARSETEST=');
        $this->assertSame([''], EnvHelper::parseArray('PARSETEST', null, []), 'Did not get empty string as expected when env set to empty');

        putenv('PARSETEST=*');
        $this->assertSame('*', EnvHelper::parseArray('PARSETEST', null, ['*', '*']), 'Did not properly ignore exclude values');

        // clean the environment
        putenv('PARSETEST');
    }
}
