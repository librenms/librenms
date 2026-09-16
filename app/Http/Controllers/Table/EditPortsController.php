<?php

/*
 * EditPortsController.php
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

namespace App\Http\Controllers\Table;

use App\Models\Port;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use LibreNMS\Enum\IfOperStatus;

/**
 * @extends TableController<Port>
 */
class EditPortsController extends TableController
{
    public function rules(): array
    {
        return [
            'device_id' => 'required|int',
            'device_group' => 'nullable|int',
            'eventtype' => 'nullable|string',
        ];
    }

    public function searchFields(Request $request): array
    {
        return ['ifName', 'ifAlias', 'ifDescr'];
    }

    protected function sortFields(Request $request): array
    {
        return ['ifIndex', 'ifName', 'ifAdminStatus', 'ifOperStatus', 'ifSpeed', 'ifAlias'];
    }

    protected function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', Port::class);

        return Port::hasAccess($request->user())
            ->where('device_id', $request->input('device_id'))
            ->with('groups');
    }

    /**
     * @param  Port  $model
     * @return array<string, scalar>
     */
    public function formatItem(Model $model): array
    {
        $is_port_bad = $model->ifAdminStatus != IfOperStatus::Down && $model->ifOperStatus != IfOperStatus::Up;
        $do_we_care = ($model->ignore || $model->disabled) ? false : $is_port_bad;
        $out_of_sync = $do_we_care ? "class='red'" : '';
        $tune = $model->device->getAttrib('ifName_tune:' . $model->ifName) == 'true' ? 'checked' : '';
        $ifAlias_override = $model->device->getAttrib('ifName:' . $model->ifName);

        $port_group_options = '';
        foreach ($model->groups as $group) {
            /** @var \App\Models\PortGroup $group */
            $port_group_options .= '<option value="' . $group->id . '" selected>' . htmlentities((string) $group->name) . '</option>';
        }

        $ifAdminStatus = $model->ifAdminStatus instanceof IfOperStatus ? $model->ifAdminStatus->value : '';
        $ifOperStatus = $model->ifOperStatus instanceof IfOperStatus ? $model->ifOperStatus->value : '';

        return [
            'ifIndex' => $model->ifIndex,
            'ifName' => htmlentities($model->getLabel()),
            'ifAdminStatus' => htmlentities($ifAdminStatus),
            'ifOperStatus' => '<span id="operstatus_' . $model->port_id . '" ' . $out_of_sync . '>' . htmlentities($ifOperStatus) . '</span>',
            'disabled' => '<input type="checkbox" class="disable-check" data-size="small" name="disabled_' . $model->port_id . '"' . ($model->disabled ? 'checked' : '') . '>
                               <input type="hidden" name="olddis_' . $model->port_id . '" value="' . ($model->disabled ? 1 : 0) . '"">',
            'ignore' => '<input type="checkbox" class="ignore-check" data-size="small" name="ignore_' . $model->port_id . '"' . ($model->ignore ? 'checked' : '') . '>
                               <input type="hidden" name="oldign_' . $model->port_id . '" value="' . ($model->ignore ? 1 : 0) . '"">',
            'port_tune' => '<input type="checkbox" name="override_config" data-attrib="ifName_tune:' . htmlentities((string) $model->ifName) . '" data-device_id="' . $model->device_id . '" data-size="small" ' . $tune . '>',
            'ifAlias' => '<div class="form-group has-feedback"><input class="form-control input-sm" name="if-alias" data-device_id="' . $model->device_id . '" data-port_id="' . $model->port_id . '" data-ifName="' . htmlentities((string) $model->ifName) . '" value="' . htmlentities((string) $model->ifAlias) . '"><span class="form-control-feedback"><i class="fa ' . ($ifAlias_override ? 'fa-pencil' : '') . '" aria-hidden="true"></i></span></div>',
            'ifSpeed' => '<div class="form-group has-feedback"><input type="text" pattern="[0-9]*" inputmode="numeric" class="form-control input-sm" name="if-speed" data-device_id="' . $model->device_id . '" data-port_id="' . $model->port_id . '" data-ifName="' . htmlentities((string) $model->ifName) . '" value="' . $model->ifSpeed . '"><span class="form-control-feedback"><i class="fas" aria-hidden="true"></i></span></div>',
            'portGroup' => '<div class="form-group has-feedback"><select class="input-sm port_group_select" name="port_group_' . $model->port_id . '[]"  data-port_id="' . $model->port_id . '" multiple>' . $port_group_options . '</select></div>',
        ];
    }
}
