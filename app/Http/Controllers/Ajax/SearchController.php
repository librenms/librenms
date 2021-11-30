<?php
/*
 * SearchController.php
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

namespace App\Http\Controllers\Ajax;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LibreNMS\Config;

abstract class SearchController
{
    public function __invoke(Request $request): JsonResponse
    {
        $search = $request->get('search');
        if (empty($search)) {
            return new JsonResponse;
        }

        $query = $this->buildQuery($search, $request)
            ->limit((int) Config::get('webui.global_search_result_limit'));

        return response()->json($query->get()->map([$this, 'formatItem']));
    }

    abstract public function buildQuery(string $search, Request $request): Builder;

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $item
     * @return array
     */
    abstract public function formatItem($item): array;
}
