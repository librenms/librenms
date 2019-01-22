<?php

/**
 * FileLock.php
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use LibreNMS\Config;
use LibreNMS\Exceptions\LockException;
use LibreNMS\Interfaces\Lock;

class FileLock implements Lock
{
    private $name;
    private $file;
    /**
     * @var resource | false
     */
    private $handle;

    private $acquired = false;

    private function __construct($lock_name)
    {
        $install_dir = Config::get('install_dir');

        $this->name = $lock_name;
        $this->file = "$install_dir/.$lock_name.lock";
        $this->handle = fopen($this->file, "w+");
    }

    public function __destruct()
    {
        $this->release();
    }

    /**
     * Release the lock.
     */
    public function release()
    {
        if (!$this->acquired) {
            return;
        }

        if (is_resource($this->handle)) {
            flock($this->handle, LOCK_UN);
            fclose($this->handle);
        }
        if (file_exists($this->file)) {
            unlink($this->file);
        }
    }

    /**
     * Given a lock name, try to acquire the lock.
     * On success return a Lock object, or on failure return false.
     * @param string $lock_name Name of lock
     * @param int $wait Try for this many seconds to see if we can acquire the lock.  Default is no wait. A negative timeout will wait forever.
     * @param int $expire Expire is unsupported for file lock at this time.
     * @return Lock
     * @throws LockException
     */
    public static function lock($lock_name, $wait = 0, $expire = 0)
    {
        $lock = new self($lock_name);
        if ($lock->handle === false) {
            throw new LockException("Failed to acquire lock $lock_name");
        }

        // try to acquire the lock each second until we reach the timeout, once if timeout is 0, forever if timeout < 0
        for ($i = 0; $i <= $wait || $wait < 0; $i++) {
            if (flock($lock->handle, $wait < 0 ? LOCK_EX : LOCK_EX | LOCK_NB)) {
                $lock->acquired = true;
                return $lock;
            }

            if ($wait) {
                sleep(1);
            }
        }

        throw new LockException("Failed to acquire lock $lock_name");
    }

    /**
     * Given a lock name, try to acquire the lock, exiting on failure.
     * On success return a Lock object.
     * @param string $lock_name Name of lock
     * @param int $timeout Try for this many seconds to see if we can acquire the lock.  Default is no wait. A negative timeout will wait forever.
     * @return \LibreNMS\Interfaces\Lock|false
     */
    public static function lockOrDie($lock_name, $timeout = 0)
    {
        try {
            return self::lock($lock_name, $timeout);
        } catch (LockException $e) {
            echo $e->getMessage() . PHP_EOL;
            exit(1);
        }
    }

    /**
     * Renew an expiring lock
     *
     * @param int $expiration number of seconds to hold lock for (null to cancel expiration)
     */
    public function renew($expiration)
    {
        echo "Unsupported";
        // TODO: Implement renew() method.
    }
}
