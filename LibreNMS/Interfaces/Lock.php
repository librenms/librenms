<?php
/**
 * Lock.php
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

namespace LibreNMS\Interfaces;

interface Lock
{
    /**
     * Release the lock.
     */
    public function release();

    /**
     * Given a lock name, try to acquire the lock.
     * On success return a Lock object, or on failure return false.
     * @param string $lock_name Name of lock
     * @param int $wait Try for this many seconds to see if we can acquire the lock.  Default is no wait. A negative timeout will wait forever.
     * @return \LibreNMS\Interfaces\Lock|false
     */
    public static function lock($lock_name, $wait = 0);

    /**
     * Renew an expiring lock
     *
     * @param int $expiration number of seconds to hold lock for (null to cancel expiration)
     */
    public function renew($expiration);

    /**
     * Given a lock name, try to acquire the lock, exiting on failure.
     * On success return a Lock object.
     * @param string $lock_name Name of lock
     * @param int $timeout Try for this many seconds to see if we can acquire the lock.  Default is no wait. A negative timeout will wait forever.
     * @return \LibreNMS\Interfaces\Lock
     */
    public static function lockOrDie($lock_name, $timeout = 0);
}
