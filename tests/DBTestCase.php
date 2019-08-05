<?php
/**
 * DBTestCase.php
 *
 * Base Test Case for Database tests
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

abstract class DBTestCase extends LaravelTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->dbSetUp();
        set_debug(false);
    }

    public function tearDown(): void
    {
        $this->dbTearDown();
        parent::tearDown();
    }
}
