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

class EditPortsController extends TableController
{
    public function rules()
    {
        return [
            'device_id' => 'required|int',
            'device_group' => 'nullable|int',
            'eventtype' => 'nullable|string',
        ];
    }

    public function searchFields($request)
    {
        return ['ifName', 'ifAlias', 'ifDescr'];
    }

    protected function sortFields($request)
    {
        return ['ifIndex', 'ifName', 'ifAdminStatus', 'ifOperStatus', 'ifSpeed', 'ifAlias'];
    }

    protected function baseQuery($request)
    {
        return \App\Models\Port::where('device_id', $request->get('device_id'))
            ->with('groups');
    }

    /**
     * @param  \App\Models\Port  $port
     * @return array
     */
    public function formatItem($port)
    {
        $is_port_bad = $port->ifAdminStatus != 'down' && $port->ifOperStatus != 'up';
        $do_we_care = ($port->ignore || $port->disabled) ? false : $is_port_bad;
        $out_of_sync = $do_we_care ? "class='red'" : '';
        $tune = $port->device->getAttrib('ifName_tune:' . $port->ifName) == 'true' ? 'checked' : '';

        $port_group_options = '';
        foreach ($port->groups as $group) {
            /** @var \App\Models\PortGroup $group */
            $port_group_options .= '<option value="' . $group->id . '" selected>' . $group->name . '</option>';
        }

        return [
            'ifIndex'          => $port->ifIndex,
            'ifName'           => $port->getLabel(),
            'ifAdminStatus'    => $port->ifAdminStatus,
            'ifOperStatus'     => '<span id="operstatus_' . $port->port_id . '" ' . $out_of_sync . '>' . $port->ifOperStatus . '</span>',
            'disabled'         => '<input type="checkbox" class="disable-check" data-size="small" name="disabled_' . $port->port_id . '"' . ($port->disabled ? 'checked' : '') . '>
                               <input type="hidden" name="olddis_' . $port->port_id . '" value="' . ($port->disabled ? 1 : 0) . '"">',
            'ignore'           => '<input type="checkbox" class="ignore-check" data-size="small" name="ignore_' . $port->port_id . '"' . ($port->ignore ? 'checked' : '') . '>
                               <input type="hidden" name="oldign_' . $port->port_id . '" value="' . ($port->ignore ? 1 : 0) . '"">',
            'port_tune'        => '<input type="checkbox" name="override_config" data-attrib="ifName_tune:' . $port->ifName . '" data-device_id="' . $port->device_id . '" data-size="small" ' . $tune . '>',
            'ifAlias'          => '<div class="form-group"><input class="form-control input-sm" name="if-alias" data-device_id="' . $port->device_id . '" data-port_id="' . $port->port_id . '" data-ifName="' . $port->ifName . '" value="' . $port->ifAlias . '"><span class="form-control-feedback"><i class="fa" aria-hidden="true"></i></span></div>',
            'ifSpeed'          => '<div class="form-group has-feedback"><input type="text" pattern="[0-9]*" inputmode="numeric" class="form-control input-sm" name="if-speed" data-device_id="' . $port->device_id . '" data-port_id="' . $port->port_id . '" data-ifName="' . $port->ifName . '" value="' . $port->ifSpeed . '"><span class="form-control-feedback"><i class="fa" aria-hidden="true"></i></span></div>',
            'portGroup'        => '<div class="form-group has-feedback"><select class="input-sm port_group_select" name="port_group_' . $port->port_id . '[]"  data-port_id="' . $port->port_id . '" multiple>' . $port_group_options . '</select></div>',
        ];
    }
}
