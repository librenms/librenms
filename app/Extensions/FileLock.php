<?php
/**
 * FileLock.php
 *
 * Atomic locking.  Only expected to work on a single system.
 * For multi-system use Redis or Memcache locks.
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Extensions;

use Illuminate\Cache\Lock;

class FileLock extends Lock
{
    protected $store;
    protected $prefix = 'file_lock_';
    protected $controlLock;
    protected $controlHandle;
    protected $wait;

    public function __construct(LockingFileStore $store, $name, $seconds, $owner = null)
    {
        parent::__construct($name, $seconds, $owner);
        $this->store = $store;
        $this->controlLock = $this->controlPath($this->key());
        $this->wait = config('cache.stores.file.control.wait');

    }

    protected function getCurrentOwner()
    {
        return $this->store->get($this->key())['owner'] ?? null;
    }

    public function forceRelease()
    {
        return $this->store->forget($this->key());
    }

    public function acquire()
    {
        return $this->atomic(function () {
            if ($this->store->get($this->key()) !== null) {
                return false;
            }

            return $this->store->put($this->key(), [
                'owner' => $this->owner
            ], $this->seconds);
        });
    }

    public function release()
    {
        return $this->atomic(function () {
            $owner = $this->store->get($this->key())['owner'] ?? null;
            return $owner === $this->owner ? $this->forceRelease() : false;
        });
    }

    private function atomic(callable $function)
    {
        $result = false;

        if ($this->acquireControlLock()) {
            $result = $function();
            $this->releaseControlLock();
        }

        return $result;
    }

    private function acquireControlLock()
    {
        $this->ensureControlDirectoryExists($this->controlLock);
        $this->controlHandle = fopen($this->controlLock, 'c');

        if ($this->controlHandle) {
            // try to acquire the lock each second until we reach the timeout, once if timeout is 0, forever if timeout < 0
            for ($i = 0; $i <= $this->wait || $this->wait < 0; $i++) {
                if (flock($this->controlHandle, $this->wait < 0 ? LOCK_EX : LOCK_EX | LOCK_NB)) {
                    return true;
                }

                if ($this->wait) {
                    usleep(10000); // 10ms
                }
            }
        }
        return false;
    }

    private function releaseControlLock()
    {
        if (is_resource($this->controlHandle)) {
            flock($this->controlHandle, LOCK_UN);
            fclose($this->controlHandle);
        }
        if (file_exists($this->controlLock)) {
            unlink($this->controlLock);
        }
    }

    public function __destruct()
    {
        $this->releaseControlLock();
    }

    protected function key()
    {
        return $this->prefix . $this->name;
    }

    /**
     * Get the full path for the given cache key.
     *
     * @param string $key
     * @return string
     */
    protected function controlPath($key)
    {
        $parts = array_slice(str_split($hash = sha1($key), 2), 0, 2);
        return config('cache.stores.file.control.path') . '/' . implode('/', $parts) . '/' . $hash;
    }

    /**
     * Create the file cache directory if necessary.
     *
     * @param string $path
     * @return void
     */
    protected function ensureControlDirectoryExists($path)
    {
        if (!$this->store->getFilesystem()->exists(dirname($path))) {
            $this->store->getFilesystem()->makeDirectory(dirname($path), 0777, true, true);
        }
    }
}
