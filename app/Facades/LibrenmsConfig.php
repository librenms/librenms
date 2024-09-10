<?php
/**
 * LibrenmsConfig.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Facades;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Facade;

class LibrenmsConfig extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'librenms-config';
    }

    public static function reload(): void
    {
        App::forgetInstance('librenms-config'); // clear singleton
        self::clearResolvedInstances(); // clear facade resolved instances cache
    }

    public static function populateLegacyDbCredentials()
    {
        $db = config('database.default');

        self::set('db_host', config("database.connections.$db.host", 'localhost'));
        self::set('db_name', config("database.connections.$db.database", 'librenms'));
        self::set('db_user', config("database.connections.$db.username", 'librenms'));
        self::set('db_pass', config("database.connections.$db.password"));
        self::set('db_port', config("database.connections.$db.port", 3306));
        self::set('db_socket', config("database.connections.$db.unix_socket"));
    }
}
