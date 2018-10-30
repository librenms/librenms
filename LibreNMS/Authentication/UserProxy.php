<?php
/**
 * UserProxy.php
 *
 * This pretends to be a User class like Laravel
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Authentication;

/**
 * @property int level
 * @property string username
 * @property int user_id
 */
class UserProxy
{
    public function hasGlobalAdmin()
    {
        return $_SESSION['userlevel'] >= 10;
    }

    public function hasGlobalRead()
    {
        return $_SESSION['userlevel'] >= 5;
    }

//    public function hasDeviceAdmin()
//    {
//        return $_SESSION['userlevel'] >= 7;
//    }

    public function isAdmin()
    {
        return $_SESSION['userlevel'] == 10;
    }

    public function isDemoUser()
    {
        return $_SESSION['userlevel'] == 11;
    }

    public function __get($name)
    {
        if ($name == 'level') {
            return $_SESSION['userlevel'];
        } elseif ($name == 'username') {
            return $_SESSION['username'];
        } elseif ($name == 'user_id') {
            return $_SESSION['user_id'];
        }

        return null;
    }
}
