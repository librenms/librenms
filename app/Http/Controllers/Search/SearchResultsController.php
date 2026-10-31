<?php

/**
 * SearchResultsController.php
 *
 * Base for the search page sources, returns one page of rendered results
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Models\Port;
use App\Models\User;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LibreNMS\Util\IPv6;
use LibreNMS\Util\SearchTerm;

/**
 * @template TModel of Model
 */
abstract class SearchResultsController extends Controller
{
    use CorrelatesMacs;

    /**
     * Search page sources, in display order, keyed by route suffix
     *
     * @var array<string, class-string<SearchResultsController<*>>>
     */
    public const SOURCES = [
        'devices' => DevicesResultsController::class,
        'ipv4' => Ipv4ResultsController::class,
        'ipv6' => Ipv6ResultsController::class,
        'mac' => MacResultsController::class,
        'arp' => ArpResultsController::class,
        'ndp' => NdpResultsController::class,
        'fdb' => FdbResultsController::class,
    ];

    /**
     * The search terms this source can answer, an empty search lists every entry
     */
    abstract public static function appliesTo(SearchTerm $term): bool;

    abstract public static function label(): string;

    abstract public static function icon(): string;

    /**
     * Short help shown on the source toggle
     */
    abstract public static function description(): string;

    /**
     * Every entry the user can see, in display order
     *
     * @return Builder<TModel>
     */
    abstract protected function baseQuery(User $user): Builder;

    /**
     * Limit the entries to those matching the term
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    abstract protected function search(Builder $query, SearchTerm $term): Builder;

    /**
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    abstract protected function filterDevice(Builder $query, int $deviceId): Builder;

    abstract protected function view(): string;

    public static function authorized(User $user): bool
    {
        return $user->can('viewAny', Port::class);
    }

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:255',
            'device_id' => 'nullable|integer',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|in:10,25,50,100',
        ]);

        abort_unless(static::authorized($request->user()), 403);

        $terms = SearchTerm::parseMany($validated['q'] ?? '');
        $browse = count($terms) == 1 && $terms[0]->value === '';
        $terms = array_values(array_filter($terms, static::appliesTo(...)));

        if (! $browse && empty($terms)) {
            return response()->json(['applicable' => false, 'total' => 0, 'html' => '']);
        }

        $results = $this->baseQuery($request->user())
            ->when(! $browse, fn (Builder $query) => $this->searchAny($query, $terms))
            ->when($validated['device_id'] ?? null, fn (Builder $q, $deviceId) => $this->filterDevice($q, (int) $deviceId))
            ->paginate((int) ($validated['per_page'] ?? 10));

        return response()->json([
            'applicable' => true,
            'total' => $results->total(),
            'page' => $results->currentPage(),
            'last_page' => $results->lastPage(),
            'from' => $results->firstItem(),
            'to' => $results->lastItem(),
            'html' => $results->total() ? $this->render($results, $request->user()) : '',
        ]);
    }

    /**
     * Extra data for the view, loaded for the current page of results only
     *
     * @param  Collection<int, TModel>  $items
     * @return array<string, mixed>
     */
    protected function viewData(Collection $items, User $user): array
    {
        return [];
    }

    /**
     * @param  LengthAwarePaginator<int, TModel>  $results
     */
    protected function render(LengthAwarePaginator $results, User $user): string
    {
        $items = $results->getCollection();

        return view($this->view(), ['results' => $items, ...$this->viewData($items, $user)])->render();
    }

    /**
     * Entries matching any of the terms
     *
     * @param  Builder<TModel>  $query
     * @param  non-empty-list<SearchTerm>  $terms
     * @return Builder<TModel>
     */
    private function searchAny(Builder $query, array $terms): Builder
    {
        if (count($terms) == 1) {
            return $this->search($query, $terms[0]);
        }

        return $query->where(function (Builder $query) use ($terms): void {
            foreach ($terms as $term) {
                $query->orWhere(fn (Builder $q) => $this->search($q, $term));
            }
        });
    }

    /**
     * Match an IP address column against the term: exact address, network or partial address
     *
     * @template TQuery of QueryBuilder
     *
     * @param  TQuery  $query
     * @param  string  $exact  the term formatted the way the column stores it
     * @return TQuery
     */
    protected function whereIp(QueryBuilder $query, string $column, SearchTerm $term, string $exact, string|Expression|null $alternateColumn = null): QueryBuilder
    {
        if ($range = $term->networkRange()) {
            return $query->whereRaw("HEX(INET6_ATON($column)) BETWEEN ? AND ?", $range);
        }

        if ($term->isExactIp()) {
            // fe80::1:2 is a valid address, but often typed as the beginning of a longer one
            if ($alternateColumn && $term->ip instanceof IPv6 && str_contains($term->value, '::')) {
                return $query->where(fn (QueryBuilder $q) => $q->where($column, $exact)->orWhere($alternateColumn, 'like', $term->ip->compressed() . '%'));
            }

            return $query->where($column, $exact);
        }

        return $query->where(fn (QueryBuilder $q) => $q->where($column, 'like', $term->like())
            ->when($alternateColumn, fn (QueryBuilder $q) => $q->orWhere($alternateColumn, 'like', $term->like())));
    }

    /**
     * Match a MAC address column (12 lowercase hex digits) against the term: partial MAC or vendor
     *
     * @template TQuery of QueryBuilder
     *
     * @param  TQuery  $query
     * @return TQuery
     */
    protected function whereMac(QueryBuilder $query, string $column, SearchTerm $term): QueryBuilder
    {
        if ($term->isMac()) {
            return $query->where($column, 'like', $term->isFullMac() ? $term->macHex : "%$term->macHex%");
        }

        $ouis = $term->vendorOuis();

        return $query->where(function (QueryBuilder $q) use ($column, $ouis): void {
            $q->whereIn(DB::raw("LEFT($column, 6)"), $ouis['short']);
            foreach ($ouis['long'] as $oui) {
                $q->orWhere($column, 'like', "$oui%");
            }
        });
    }
}
