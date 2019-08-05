<?php
/**
 * TestCase.php
 *
 * Base Test Case for all tests
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

use LibreNMS\Util\Snmpsim;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    use SnmpsimHelpers;

    /** @var Snmpsim snmpsim instance */
    protected $snmpsim = null;

    public function __construct($name = null, $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        // grab global $snmpsim from bootstrap and make it accessible
        global $snmpsim;
        $this->snmpsim = $snmpsim;
    }

    public function setUp(): void
    {
        parent::setUp();
        set_debug(false); // prevent warnings from stopping execution for legacy code
    }

    public function dbSetUp()
    {
        if (getenv('DBTEST')) {
            \LibreNMS\DB\Eloquent::boot();
            \LibreNMS\DB\Eloquent::setStrictMode();
            \LibreNMS\DB\Eloquent::DB()->beginTransaction();
        } else {
            $this->markTestSkipped('Database tests not enabled.  Set DBTEST=1 to enable.');
        }
    }

    public function dbTearDown()
    {
        if (getenv('DBTEST')) {
            \LibreNMS\DB\Eloquent::DB()->rollBack();
        }
    }
}
