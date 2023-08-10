<?php
/*
 * RolesController.php
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
 * @copyright  2023 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Select;

use Illuminate\Http\Request;
use Silber\Bouncer\BouncerFacade as Bouncer;

class RoleController extends SelectController
{
    protected ?string $idField = 'name';
    protected ?string $textField = 'title';

    protected function searchFields(Request $request)
    {
        return ['name'];
    }

    protected function baseQuery(Request $request)
    {
        return Bouncer::role()
            ->whereRaw('1 = ' . ((int) $request->user()->can('viewAny', Bouncer::role())));
    }
}
