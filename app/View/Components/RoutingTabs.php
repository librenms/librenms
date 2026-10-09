<?php

/**
 * RoutingTabs.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 LibreNMS
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\View\Components;

use App\View\Components\Device\RoutingTabs as DeviceRoutingTabs;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use LibreNMS\Util\ObjectCache;

class RoutingTabs extends Component
{
    /**
     * @var array<string, array{text: string, link: string}>
     */
    public array $tabs = [];

    public function __construct(
        public readonly ?string $tab = null,
    ) {
        $labels = DeviceRoutingTabs::getTabLabels();

        foreach (self::getRoutingTabs() as $type => $count) {
            $this->tabs[$type] = [
                'text' => ($labels[$type] ?? ucfirst($type)) . ' (' . $count . ')',
                'link' => route('routing.' . $type),
            ];
        }
    }

    /**
     * Routing types the current user can see, with the number of entries for each
     *
     * @return array<string, int>
     */
    public static function getRoutingTabs(): array
    {
        return array_filter(ObjectCache::routing());
    }

    public function render(): View|Closure|string
    {
        return view('components.routing-tabs');
    }
}
