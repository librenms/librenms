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
 * @link       http://librenms.org
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

use App\Extensions\LockingFileStore;
use Carbon\Carbon;

class LockTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->getFilestore()->flush();
    }

    public function testFileLock()
    {
        $store = $this->getFilestore();

        $lock = $store->lock('foo');
        $lock->forceRelease();
        $this->assertTrue($lock->get(), 'Failed to get lock');
        $lock->release();

        $lock = $store->lock('foo');
        $this->assertTrue($lock->get(), 'Failed to get lock after releasing');
        unset($lock);
    }

    public function testFileLockFail()
    {
        $store = $this->getFilestore();

        $lock = $store->lock('foo');
        $lock->get();

        $failed_lock = $store->lock('foo');
        $this->assertFalse($failed_lock->get(), 'Reacquired lock, oops');
    }

    public function testCannotAquireLockTwice()
    {
        $store = $this->getFilestore();
        $lock = $store->lock('foo');

        $this->assertTrue($lock->acquire());
        $this->assertFalse($lock->acquire());
    }

    public function testCanAquireLockAgainAfterExpiry()
    {
        Carbon::setTestNow(Carbon::now());
        $store = $this->getFilestore();
        $lock = $store->lock('foo', 10);
        $lock->acquire();
        Carbon::setTestNow(Carbon::now()->addSeconds(10));

        $this->assertTrue($lock->acquire());
    }

    public function testLockExpirationLowerBoundary()
    {
        Carbon::setTestNow(Carbon::now());
        $now = Carbon::now();
        $store = $this->getFilestore();
        $lock = $store->lock('foo', 10);
        $lock->acquire();
        Carbon::setTestNow(Carbon::now()->addSeconds(10)->subSecond()); // file cache only has second resolution

        $this->assertFalse($lock->acquire());
    }

    public function testLockWithNoExpirationNeverExpires()
    {
        Carbon::setTestNow(Carbon::now());
        $store = $this->getFilestore();
        $lock = $store->lock('foo');
        $lock->acquire();
        Carbon::setTestNow(Carbon::now()->addYears(100));

        $this->assertFalse($lock->acquire());
    }

    public function testCanAcquireLockAfterRelease()
    {
        $store = $this->getFilestore();
        $lock = $store->lock('foo', 10);
        $lock->acquire();

        $this->assertTrue($lock->release());
        $this->assertTrue($lock->acquire());
    }

    public function testAnotherOwnerCannotReleaseLock()
    {
        $store = $this->getFilestore();
        $owner = $store->lock('foo', 10);
        $wannabeOwner = $store->lock('foo', 10);
        $owner->acquire();

        $this->assertFalse($wannabeOwner->release());
    }

    public function testAnotherOwnerCanForceReleaseALock()
    {
        $store = $this->getFilestore();
        $owner = $store->lock('foo', 10);
        $wannabeOwner = $store->lock('foo', 10);
        $owner->acquire();
        $wannabeOwner->forceRelease();

        $this->assertTrue($wannabeOwner->acquire());
    }

    private function getFilestore()
    {
        return new LockingFileStore($this->app->make('files'),
            $this->app->make('config')->get('cache.stores.file.path'),
            $this->app->make('config')->get('cache.stores.file.permissions')
        );
    }
}
