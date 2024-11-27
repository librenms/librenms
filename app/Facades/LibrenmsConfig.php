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
use Illuminate\Support\Facades\Log;

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

    public static function invalidateAndReload(): void
    {
        self::invalidateCache();
        self::reload();

        Log::info('LibreNMS config cache cleared and config reloaded.');
    }
}
