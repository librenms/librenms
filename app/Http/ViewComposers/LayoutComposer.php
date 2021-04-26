<?php
/**
 * LayoutComposer.php
 *
 * Provides data for the main layout
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

namespace App\Http\ViewComposers;

use App\Checks;
use App\Models\UserPref;
use Illuminate\View\View;
use LibreNMS\Config;

class LayoutComposer
{
    /**
     * Bind data to the view.
     *
     * @param  View $view
     * @return void
     */
    public function compose(View $view)
    {
        // build page title
        if ($view->getFactory()->hasSection('title')) {
            // short sections escape the html entities, reverse that
            $title = html_entity_decode(trim($view->getFactory()->getSection('title')), ENT_QUOTES);
            $title = str_replace('    ', ' : ', $title);
            $title .= ' | ' . Config::get('page_title_suffix');
        } else {
            $title = Config::get('page_title_suffix');
        }

        Checks::postAuth();

        $show_menu = auth()->check();
        if ($show_menu && Config::get('twofactor') && ! session('twofactor')) {
            $show_menu = empty(UserPref::getPref(auth()->user(), 'twofactor'));
        }

        $view->with('pagetitle', $title)
            ->with('show_menu', $show_menu);
    }
}
