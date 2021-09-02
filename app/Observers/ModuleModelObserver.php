<?php
/*
 * ModuleModelObserver.php
 *
 * Displays +,-,U,. while running discovery and adding,deleting,updating, and doing nothing.
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Observers;

use Illuminate\Database\Eloquent\Model as Eloquent;

class ModuleModelObserver
{
    /**
     * Install observers to output +, -, U for models being created, deleted, and updated
     *
     * @param string|\Illuminate\Database\Eloquent\Model $model The model name including namespace
     */
    public static function observe($model)
    {
        static $observed_models = []; // keep track of observed models so we don't duplicate output
        $class = ltrim($model, '\\');

        if (! in_array($class, $observed_models)) {
            $model::observe(new static());
            $observed_models[] = $class;
        }
    }

    /**
     * @param Eloquent $model
     */
    public function saving($model)
    {
        if (! $model->isDirty()) {
            echo '.';
        }
    }

    /**
     * @param Eloquent $model
     */
    public function updated($model)
    {
        d_echo('Updated data:', 'U');
        d_echo($model->getDirty());
    }

    /**
     * @param Eloquent $model
     */
    public function created($model)
    {
        echo '+';
    }

    /**
     * @param Eloquent $model
     */
    public function deleted($model)
    {
        echo '-';
    }
}
