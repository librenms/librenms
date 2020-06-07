<?php
/**
 * InstallMenuComposer.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\ViewComposers;

use Illuminate\View\View;

class InstallMenuComposer
{
    // TODO port to Laravel 7 View Component
    private $steps = [
        'checks' => \App\Http\Controllers\Install\ChecksController::class,
        'database' => \App\Http\Controllers\Install\DatabaseController::class,
        'migrate' => \App\Http\Controllers\Install\DatabaseMigrationController::class,
        'user' => \App\Http\Controllers\Install\MakeUserController::class,
        'finish' => \App\Http\Controllers\Install\FinalizeController::class,
    ];

    /**
     * Bind data to the view.
     *
     * @param  View $view
     * @return void
     */
    public function compose(View $view)
    {
        $steps = $this->steps;
        if (is_string(config('librenms.install'))) {
            $steps = array_intersect_key($steps, array_flip(explode(',', config('librenms.install'))));
        }




        $view->with(['steps' => $steps]);
    }
}
