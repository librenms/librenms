<?php
/**
 * SimpleTableController.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Table;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

abstract class SimpleTableController extends Controller
{
    public static $base_rules = [
        'current' => 'int',
        'rowCount' => 'int',
        'searchPhrase' => 'nullable|string',
        'sort.*' => 'in:asc,desc',
    ];

    /**
     * Validate the given request with the given rules.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  array $rules
     * @param  array $messages
     * @param  array $customAttributes
     * @return array
     */
    public function validate(Request $request, array $rules = [], array $messages = [], array $customAttributes = [])
    {
        $full_rules = array_replace(self::$base_rules, $rules);

        return parent::validate($request, $full_rules, $messages, $customAttributes);
    }

    /**
     * @param array|Collection $rows
     * @param int $page
     * @param int $currentCount
     * @param int $total
     * @return \Illuminate\Http\JsonResponse
     */
    protected function formatResponse($rows, $page, $currentCount, $total)
    {
        return response()->json([
            'current' => $page,
            'rowCount' => $currentCount,
            'rows' => $rows,
            'total' => $total,
        ]);
    }
}
