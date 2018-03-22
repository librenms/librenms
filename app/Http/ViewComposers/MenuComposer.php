<?php
/**
 * Menu.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\ViewComposers;

use App\Models\DeviceGroup;
use App\Models\Package;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use LibreNMS\Config;

class MenuComposer
{
//    /**
//     * The user repository implementation.
//     *
//     * @var UserRepository
//     */
//    public function __construct(UserRepository $users)
//    {
//        // Dependencies automatically resolved by service container...
//        $this->users = $users;
//    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $vars = [];

        $vars['navbar'] = in_array(Config::get('site_style'), ['mono', 'dark']) ? 'navbar-inverse' : '';

        if ($title_image = Config::get('title_image')) {
            $vars['title_image'] = '<img src="' . $title_image . '" /></a>';
        } else {
            $vars['title_image'] = Config::get('project_name', 'LibreNMS');
        }

        $vars['device_groups'] = DeviceGroup::select('id', 'name', 'desc')->get();
        $vars['package_count'] = Package::count();

        $vars['device_types'] = auth()->user()->devices()->select('type')->distinct()->get()->pluck('type');

        if (Config::get('show_locations') && Config::get('show_locations_dropdown')) {
            $vars['locations'] = auth()->user()->devices()->select('location')->distinct()->get()->pluck('location')->filter();
        } else {
            $vars['locations'] = [];
        }

        if (Config::get('show_services')) {
            $vars['service_status'] = Service::groupBy('service_status')
                ->select('service_status', DB::raw('count(*) as count'))
                ->whereIn('service_status', [1, 2])
                ->get()
                ->keyBy('service_status');

            $warning = $vars['service_status']->get(1);
            $vars['service_warning'] = $warning ? $warning->count : 0;
            $critical = $vars['service_status']->get(2);
            $vars['service_critical'] = $critical ? $critical->count : 0;
        }


        $view->with($vars);
    }
}
