<?php
/**
 * LockTest.php
 *
 * Test Locking functionality.
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

use LibreNMS\Exceptions\LockException;
use LibreNMS\Util\FileLock;

class LockTest extends TestCase
{
    public function testFileLock()
    {
        $lock = FileLock::lock('tests');
        $lock->release();

        $new_lock = FileLock::lock('tests');
        unset($new_lock);

        FileLock::lock('tests');

        $this->expectNotToPerformAssertions();
    }

    public function testFileLockFail()
    {
        $lock = FileLock::lock('tests');

        $this->expectException('LibreNMS\Exceptions\LockException');
        $failed_lock = FileLock::lock('tests');

        $this->expectNotToPerformAssertions();
    }

    public function testFileLockWait()
    {
        $lock = FileLock::lock('tests');

        $start = microtime(true);
        $this->expectException('LibreNMS\Exceptions\LockException');
        $wait_lock = FileLock::lock('tests', 1);
        $this->assertGreaterThan(1, microtime(true) - $start, 'Lock did not wait.');

        $lock->release();

        $start = microtime(true);
        $wait_lock = FileLock::lock('tests', 5);
        $this->assertLessThan(1, microtime(true) - $start, 'Lock waited when it should not have');
    }
}
