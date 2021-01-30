<?php
/*
 * GraphService.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Graphing;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\Registrar as RouteRegistrar;
use Illuminate\Support\Str;

class GraphService
{
    private $app;
    private $config;
    /**
     * @var \Illuminate\Routing\RouteRegistrar
     */
    private $route;

    public function __construct(Application $app, Repository $config, RouteRegistrar $route)
    {
        $this->app = $app;
        $this->config = $config;
        $this->route = $route;
    }

    public function register($controller)
    {
        $instance = $this->app->make($controller);
        [$group, $name] = Str::of(class_basename($controller))->snake()->explode('_', 2);
        $group = $instance->group ?? $group;
        $name = $instance->name ?? $name;

        $this->route->prefix('graph/data')
            ->get("$group/$name", GraphDataController::class)
            ->name("graph_data.{$group}_$name")
            ->middleware('graphs')
            ->defaults('graph', $instance);
        $this->route->prefix('graph')
            ->get("$group/$name", GraphViewController::class)
            ->name("graph_view.{$group}_$name")
            ->middleware('graphs')
            ->defaults('graph', $instance);
    }
}
