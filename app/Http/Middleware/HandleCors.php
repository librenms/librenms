<?php
/**
 * HandleCors.php
 *
 * Check and load cors settings from db config at runtime
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Middleware;

use Asm89\Stack\CorsService;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;

class HandleCors extends \Fruitcake\Cors\HandleCors
{
    private $map = [
        'allowmethods' => 'allowed_methods',
        'origin' => 'allowed_origins',
        'allowheaders' => 'allowed_headers',
        'exposeheaders' => 'exposed_headers',
    ];

    public function __construct(Container $container)
    {
        // load legacy config settings before booting the CorsService
        if (\LibreNMS\Config::get('api.cors.enabled')) {
            $laravel_config = $container['config']->get('cors');
            $legacy = \LibreNMS\Config::get('api.cors');

            $laravel_config['paths'][] = 'api/*';

            foreach ($this->map as $config_key => $option_key) {
                if (isset($legacy[$config_key])) {
                    $laravel_config[$option_key] = Arr::wrap($legacy[$config_key]);
                }
            }
            $laravel_config['max_age'] = $legacy['maxage'] ?? $laravel_config['max_age'];
            $laravel_config['supports_credentials'] = $legacy['allowcredentials'] ?? $laravel_config['supports_credentials'];

            $container['config']->set('cors', $laravel_config);
        }

        $cors = $container->make(CorsService::class);
        parent::__construct($cors, $container);
    }
}
