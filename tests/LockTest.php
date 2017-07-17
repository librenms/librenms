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

use LibreNMS\FileLock;
use PHPUnit\Framework\TestCase;

class LockTest extends TestCase
{
    public function testFileLock()
    {
        $lock = FileLock::lock('tests');
        $this->assertNotFalse($lock, 'Failed to acquire initial lock!');
        $lock->release();

        $new_lock = FileLock::lock('tests');
        $this->assertNotFalse($new_lock, 'Failed to release the lock with release()');
        unset($new_lock);

        $this->assertNotFalse(FileLock::lock('tests'), 'Failed to remove lock when the lock object was destroyed');
    }

    public function testFileLockFail()
    {
        $lock = FileLock::lock('tests');
        $this->assertNotFalse($lock, 'Failed to acquire initial lock!');

        $failed_lock = FileLock::lock('tests');
        $this->assertFalse($failed_lock, 'Additional lock attempt did not fail');
    }

    public function testFileLockWait()
    {
        $lock = FileLock::lock('tests');
        $this->assertNotFalse($lock, 'Failed to acquire initial lock!');

        $start = microtime(true);
        $wait_lock = FileLock::lock('tests', 1);
        $this->assertGreaterThan(1, microtime(true) - $start, 'Lock did not wait.');
        $this->assertFalse($wait_lock, 'Waiting lock attempt did not fail');

        $lock->release();

        $start = microtime(true);
        $wait_lock = FileLock::lock('tests', 5);
        $this->assertLessThan(1, microtime(true) - $start, 'Lock waited when it should not have');
        $this->assertNotFalse($wait_lock, 'Second wait lock did not succeed');
    }
}
