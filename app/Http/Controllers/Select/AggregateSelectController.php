<?php

/**
 * AggregateSelectController.php
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
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Select;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

abstract class AggregateSelectController extends SelectController
{
    /** @var array<array{text: string, controller: class-string, prefix: string}> */
    protected array $groups = [];

    public function __invoke(Request $request)
    {
        $groups = collect($this->groups)->map(fn (array $group) => [
            ...$group,
            'controller' => new $group['controller'](),
        ]);

        $this->validate($request, $groups->flatMap(fn ($g) => $g['controller']->rules())->all());
        $limit = $request->integer('limit', 50);
        $page = $request->integer('page', 1);
        $hasMore = false;

        $groups = $groups->map(function ($group) use ($request, $limit, &$hasMore, &$page) {
            if ($hasMore) { // skip if we've already paginated a previous group
                return $group;
            }

            $paginator = $this->buildQuery($group['controller'], $request)->paginate($limit, page: $page);
            $group['items'] = $paginator;
            $hasMore = $paginator->hasMorePages();

            if (! $hasMore) {
                $page = max(1,
                    $page - $paginator->lastPage() + 1); // done with this controller, remove it's pages from the total page count
            }

            return $group;
        });

        return $this->formatResponse($groups, $hasMore);
    }

    public function baseQuery(Request $request)
    {
        return \App\Models\Device::query(); // unused
    }

    /**
     * @param  array{text: string, controller: SelectController, prefix: string, items: Paginator}|Model  $group
     * @return array
     */
    public function formatItem($group): array
    {
        if (! isset($group['items'])) {
            return [];
        }

        $items = $group['items']->getCollection()->map(fn ($item
        ) => $group['controller']->formatItem($item))->map(function ($item) use ($group) {
            $item['id'] = $group['prefix'] . $item['id'];

            return $item;
        })->values()->toArray();

        return [
            // only show header once per group
            'text' => $group['items']->onFirstPage() ? $group['text'] : null,
            'children' => $items,
        ];
    }
}
