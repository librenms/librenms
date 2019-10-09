<?php
/**
 * Version.php
 *
 * Get version info about LibreNMS and various components/dependencies
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use LibreNMS\DB\Eloquent;

class Version
{
    // Update this on release
    const VERSION = '1.56';

    protected $is_git_install = false;

    public function __construct()
    {
        $this->is_git_install = Git::repoPresent() && Git::binaryExists();
    }

    public static function get()
    {
        return new static;
    }

    public function local()
    {
        if ($this->is_git_install && $version = $this->fromGit()) {
            return $version;
        }

        return self::VERSION;
    }

    public function database()
    {
        if (Eloquent::isConnected()) {
            try {
                $query = Eloquent::DB()->table('migrations');
                return [
                    'last' => $query->orderBy('id', 'desc')->value('migration'),
                    'total' => $query->count(),
                ];
            } catch (\Exception $e) {
                return ['last' => 'No Schema', 'total' => 0];
            }
        }

        return ['last' => 'Not Connected', 'total' => 0];
    }

    private function fromGit()
    {
        return rtrim(shell_exec('git describe --tags 2>/dev/null'));
    }

    public function gitChangelog()
    {
        return $this->is_git_install
            ? rtrim(shell_exec('git log -10'))
            : '';
    }

    public function gitDate()
    {
        return $this->is_git_install
            ? rtrim(shell_exec("git show --pretty='%ct' -s HEAD"))
            : '';
    }
}
