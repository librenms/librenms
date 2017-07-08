<?php
/**
 * MysqlLock.php
 *
 * Create arbitrary Mysql named locks to synchronize multiple processes.  Not for locking tables.
 * Warning! This will not work with Replication if statement based replication is enabled or Galera setups.
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

namespace LibreNMS;

class MysqlLock
{
    private $name;

    private function __construct($lock_name)
    {
        $this->name = $lock_name;
    }

    public function __destruct()
    {
        $this->release();
    }

    /**
     * Given a lock name, try to acquire the lock.
     * On success return a MysqlLock object, or on failure return false.
     * @param string $lock_name Name of lock
     * @param int $timeout Try for this many seconds to see if we can acquire the lock.  Default is no wait. A negative timeout will wait forever.
     * @return self|false
     */
    public static function lock($lock_name, $timeout = 0)
    {
        // GET_LOCK only returns 0 if another session has the lock, So we need to use IS_FREE_LOCK
        // try to acquire the lock each second until we reach the timeout, once if timeout is 0, forever if timeout < 0
        for ($i = 0; $i <= $timeout || $timeout < 0; $i++) {
            if (dbFetchCell("SELECT IS_FREE_LOCK(?)", array($lock_name))) {
                if (dbFetchCell("SELECT GET_LOCK(?, 0)", array($lock_name))) {
                    return new self($lock_name);
                }
            }

            if ($timeout) {
                sleep(1);
            }
        }

        return false;
    }

    /**
     * Release the lock.
     */
    public function release()
    {
        dbQuery("DO RELEASE_LOCK(?)", array($this->name));
    }
}
