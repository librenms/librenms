<?php
/**
 * MemcacheLock.php
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

class MemcacheLock implements Lock
{
    private $namespace = 'lock';
    private $lock_name;
    private $poller_name;
    private $memcached;
    private $host;
    private $port;

    private function __construct($lock_name)
    {
        if (!class_exists('Memcached')) {
            throw new LockException("Missing PHP Memcached extension, this is required for distributed polling.");
        }

        // check all config vars or fallback
        $this->host = Config::get('distributed_poller_memcached_host', Config::get('memcached.host', 'localhost'));
        $this->port = Config::get('distributed_poller_memcached_port', Config::get('memcached.port', 11211));

        $this->lock_name = "$this->namespace.$lock_name";
        $this->poller_name = Config::get('distributed_poller_name');
        $this->memcached = new \Memcached();
        $this->memcached->addServer($this->host, $this->port);
    }

    /**
     * Given a lock name, try to acquire the lock, exiting on failure.
     * On success return a Lock object.
     * @param string $lock_name Name of lock
     * @param int $timeout Try for this many seconds to see if we can acquire the lock.  Default is no wait. A negative timeout will wait forever.
     * @return Lock
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
     * Given a lock name, try to acquire the lock.
     * On success return a Lock object, or on failure return false.
     * @param string $lock_name Name of lock
     * @param int $wait Try for this many seconds to see if we can acquire the lock.  Default is no wait. A negative timeout will wait forever.
     * @param int $expiration number of seconds to hold lock for, default is forever
     * @return Lock
     * @throws LockException
     */
    public static function lock($lock_name, $wait = 0, $expiration = null)
    {
        $lock = new self($lock_name);

        if (!$lock->isConnected()) {
            throw new LockException("Could not connect to memcached ($lock->host:$lock->port)");
        }

        $owner = true;
        for ($i = 0; $i <= $wait || $wait < 0; $i++) {
            $owner = $lock->memcached->get($lock->lock_name);
            if ($owner == false) {
                break;  // try to acquire the lock
            }
            sleep(1);
        }

        if ($owner) {
            if ($owner == $lock->poller_name) {
                throw new LockException("This poller ($owner) already owns the lock: $lock->lock_name");
            }
            throw new LockException("Lock $lock->lock_name already acquired by $owner");
        }

        $lock->memcached->set($lock->lock_name, $lock->poller_name, $expiration);
        $owner = $lock->memcached->get($lock->lock_name);
        if ($owner != $lock->poller_name) {
            throw new LockException("Another poller ($owner) has lock: $lock->lock_name");
        }

        return $lock;
    }

    private function isConnected()
    {
        return $this->memcached->getVersion() != false;
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
        $this->memcached->delete($this->lock_name);
    }

    /**
     * Renew an expiring lock
     *
     * @param int $expiration number of seconds to hold lock for (null to cancel expiration)
     */
    public function renew($expiration)
    {
        $owner = $this->memcached->get($this->lock_name);
        if ($owner == $this->poller_name) {
            $this->memcached->set($this->lock_name, $this->poller_name, $expiration);
        }
    }
}
