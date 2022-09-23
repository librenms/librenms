<?php
/*
 * PortPluginTab.php
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

namespace App\Plugins\Hooks;

use App\Models\Port;
use App\Models\User;
use App\Plugins\Hook;
use Illuminate\Support\Str;

abstract class PortTabHook implements Hook
{
    /** @var string */
    public $view = 'resources.views.port-tab';

    public function authorize(User $user, Port $port, array $settings): bool
    {
        return true;
    }

    public function data(Port $port): array
    {
        return [
            'title' => __CLASS__,
            'port'  => $port,
        ];
    }

    final public function handle(string $pluginName, Port $port): \Illuminate\Contracts\View\View
    {
        return view(Str::start($this->view, "$pluginName::"), $this->data($port));
    }
}
