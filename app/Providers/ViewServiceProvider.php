<?php
/**
 * ViewServiceProvider.php
 *
 * Safely fall back to Laravel view service provider if String Blade Compiler is missing
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

namespace App\Providers;

class ViewServiceProvider extends \Illuminate\View\ViewServiceProvider
{
    public function register()
    {
        if (class_exists('Wpb\String_Blade_Compiler\ViewServiceProvider')) {
            $this->app->register('Wpb\String_Blade_Compiler\ViewServiceProvider');
        } else {
            $this->app->register('Illuminate\View\ViewServiceProvider');
        }
    }

    public function boot()
    {
        if (!class_exists('Wpb\String_Blade_Compiler\ViewServiceProvider')) {
            \Toastr::error('Dependencies missing, check <a href="validate">validate</a>');
        }
    }
}
