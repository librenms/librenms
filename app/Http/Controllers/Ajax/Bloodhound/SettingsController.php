<?php
/**
 * SettingsController.php
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Ajax\Bloodhound;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use LibreNMS\Util\DynamicConfig;

class SettingsController extends Controller
{
    public function __invoke(Request $request, DynamicConfig $config)
    {
        $this->validate($request, [
            'term' => 'required|string',
        ]);

        $term = $request->get('term');
        $list = $this->levSort($config->all()->filter->isValid(), $term);

        return response()->json($list->take(20));
    }

    /**
     * @param Collection $items
     * @param string $term
     * @return Collection
     */
    private function levSort(Collection $items, $term)
    {
        $list = $items->sortBy(function ($item) use ($term) {
            $lev = 0;

            // tokenize the words
            $words = explode('.', $item->name) + explode(' ', __($item->description)) + explode(' ', __($item->help)) +
                explode(' ', __('settings.section.' . $item->section)) + explode(' ', __('settings.groups.' . $item->group));

            foreach (explode(' ', $term) as $search_word) {
                $term_lev = [];
                foreach ($words as $word) {
                    $term_lev[] = levenshtein($word, $search_word);
                }
                $lev += min($term_lev);
            }

            return $lev;
        });
        return $list;
    }
}
