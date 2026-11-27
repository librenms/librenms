<?php

/**
 * init.php
 *
 * Load includes and initialize needed things
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
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

use App\Facades\LibrenmsConfig;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Laravel;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$install_dir = realpath(__DIR__ . '/..');
chdir($install_dir);

// composer autoload
if (! is_file($install_dir . '/vendor/autoload.php')) {
    require_once $install_dir . '/includes/common.php';
    c_echo("%RError: Missing dependencies%n, run: %B./scripts/composer_wrapper.php install --no-dev%n\n\n");
}
require_once $install_dir . '/vendor/autoload.php';

// function only files
require_once $install_dir . '/includes/common.php';
require_once $install_dir . '/includes/dbFacile.php';
require_once $install_dir . '/includes/snmp.inc.php';
require_once $install_dir . '/includes/services.inc.php';
require_once $install_dir . '/includes/functions.php';
require_once $install_dir . '/includes/html/functions.inc.php';

// Boot Laravel, standalone v1 plugin web scripts set $init_modules = ['web', 'auth']
if (in_array('web', $init_modules ?? [])) {
    Laravel::bootWeb();
} else {
    Laravel::bootCli();
}
Debug::set($debug ?? false); // override laravel configured settings (hides legacy errors too)

\LibreNMS\DB\Eloquent::setStrictMode(false); // disable strict mode for legacy code...

if (is_numeric(LibrenmsConfig::get('php_memory_limit')) && LibrenmsConfig::get('php_memory_limit') > 128) {
    ini_set('memory_limit', LibrenmsConfig::get('php_memory_limit') . 'M');
}
