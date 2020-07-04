<?php
/**
 * LockingFileStore.php
 *
 * Extension of Laravel FileStore to add locking.
 * Locking only valid on one system.
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

use Illuminate\Cache\FileStore;
use Illuminate\Contracts\Cache\LockProvider;

class LockingFileStore extends FileStore implements LockProvider
{

    public function lock($name, $seconds = 0, $owner = null)
    {
        return new FileLock($this, $name, $seconds, $owner);
    }

    public function restoreLock($name, $owner)
    {
        return $this->lock($name, 0, $owner);
    }

    /**
     * Get the full path for the given cache key.
     *
     * @param  string  $key
     * @return string
     */
    public function path($key)
    {
        return parent::path($key);
    }

    /**
     * Create the file cache directory if necessary.
     *
     * @param  string  $path
     * @return void
     */
    protected function ensureCacheDirectoryExists($path)
    {
        parent::ensureCacheDirectoryExists($path);
    }
}
