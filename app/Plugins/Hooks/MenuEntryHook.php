<?php
/*
 * PluginMenuEntry.php
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

use App\Models\User;

abstract class MenuEntryHook
{
    public $view = 'resources.views.menu';

    public function authorize(User $user): bool
    {
        return true;
    }

    public function data(): array
    {
        return [];
    }

    final public function handle(): \View
    {
        \View::addLocation(\PluginManager::pluginPath($this));

        return [$this->view, $this->data()];
    }
}
