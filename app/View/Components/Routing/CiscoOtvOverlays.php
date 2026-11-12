<?php

/**
 * CiscoOtvOverlays.php
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

namespace App\View\Components\Routing;

use App\Models\Component as ComponentModel;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\View\Component;

/**
 * @phpstan-type OtvItem array{id: int, label: string, detail: string, error: string, is_normal: bool}
 * @phpstan-type OtvOverlay array{id: int, label: string, detail: string, error: string, is_normal: bool, adjacencies: list<OtvItem>}
 */
class CiscoOtvOverlays extends Component
{
    /** @var list<OtvOverlay> */
    public array $overlays = [];

    /**
     * @param  EloquentCollection<int, ComponentModel>  $components  Cisco-OTV components for a single device
     */
    public function __construct(
        EloquentCollection $components,
        public readonly string $id = 'overlays',
    ) {
        $components->loadMissing('prefs');

        $items = $components->map(fn (ComponentModel $component) => [
            'component' => $component,
            'prefs' => $component->prefs->pluck('value', 'attribute')->all(),
        ]);

        foreach ($items->where('prefs.otvtype', 'overlay') as $overlay) {
            $index = $overlay['prefs']['index'] ?? null;
            $adjacencies = $items->filter(fn ($item) => ($item['prefs']['otvtype'] ?? null) === 'adjacency' && ($item['prefs']['index'] ?? null) == $index);

            $this->overlays[] = $this->formatItem($overlay['component'], $overlay['prefs']['transport'] ?? '') + [
                'adjacencies' => $adjacencies->map(fn ($adjacency) => $this->formatItem($adjacency['component'], $adjacency['prefs']['endpoint'] ?? ''))->values()->all(),
            ];
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.routing.cisco-otv-overlays');
    }

    /**
     * @return OtvItem
     */
    private function formatItem(ComponentModel $component, string $detail): array
    {
        return [
            'id' => $component->id,
            'label' => (string) $component->label,
            'detail' => $detail,
            'error' => (string) $component->error,
            'is_normal' => empty($component->status) && empty($component->ignore) && empty($component->disabled),
        ];
    }
}
