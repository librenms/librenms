<?php
/**
 * PageLinks.php
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
 *
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\View\Components\Device;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

class PageLinks extends Component
{
    public array $primaryDeviceLink;
    public array $deviceLinks;

    public function __construct(
        public readonly Device $device,
        public readonly string $currentTab = '',
        public readonly array $dropdownLinks = [],
    ) {
        $this->deviceLinks = $this->deviceLinkMenu($device, $currentTab);
        $primary_device_link_name = LibrenmsConfig::get('html.device.primary_link', 'edit');
        if (! isset($this->deviceLinks[$primary_device_link_name])) {
            $primary_device_link_name = array_key_first($this->deviceLinks);
        }
        $this->primaryDeviceLink = $this->deviceLinks[$primary_device_link_name];
        unset($this->deviceLinks[$primary_device_link_name], $primary_device_link_name);
    }

    private function deviceLinkMenu(Device $device, string $currentTab): array
    {
        $device_links = [];

        if (Gate::allows('update', $device)) {
            $suffix = 'edit';
            $title = __('Edit');

            // check if metric has more specific edit page
            $path = \Request::path();
            if (preg_match('#health/metric=(\w+)#', $path, $matches)) {
                if ($this->editTabExists($matches[1])) {
                    $currentTab = $matches[1];
                } elseif ($this->editTabExists($matches[1] . 's')) {
                    $currentTab = $matches[1] . 's';
                }
            } elseif (preg_match('#device/\d+/ports/transceivers#', $path)) {
                $currentTab = 'transceivers';
            }

            // check if edit page exists
            if ($this->editTabExists($currentTab)) {
                $suffix .= "/section=$currentTab";
                $title .= ' ' . __(ucfirst($currentTab));
            }

            $device_links['edit'] = [
                'icon' => 'fa-gear',
                'url' => route('device', [$device->device_id, $suffix]),
                'title' => $title,
                'external' => false,
            ];
        }

        // User defined device links
        foreach (array_values(Arr::wrap(LibrenmsConfig::get('html.device.links'))) as $index => $link) {
            $device_links['custom' . ($index + 1)] = [
                'icon' => $link['icon'] ?? 'fa-external-link',
                'url' => Blade::render($link['url'], ['device' => $device]),
                'title' => $link['title'],
                'external' => $link['external'] ?? true,
            ];
        }

        // Web
        $http_port = $device->attribs->firstWhere('attrib_type', 'override_device_http_port') ? ':' . $device->attribs->firstWhere('attrib_type', 'override_device_http_port')->attrib_value : '';
        $device_links['web'] = [
            'icon' => 'fa-globe',
            'url' => 'https://' . $device->hostname . $http_port,
            'title' => __('Web'),
            'external' => true,
            'onclick' => 'http_fallback(this); return false;',
        ];

        // IPMI
        if ($device->attribs->firstWhere('attrib_type', 'ipmi_hostname')) {
            $device_links['ipmi'] = [
                'icon' => 'fa-microchip',
                'url' => 'https://' . $device->attribs->firstWhere('attrib_type', 'ipmi_hostname')->attrib_value,
                'title' => __('IPMI'),
                'external' => true,
                'onclick' => 'http_fallback(this); return false;',
            ];
        }

        // SSH
        $ssh_port = $device->attribs->firstWhere('attrib_type', 'override_device_ssh_port') ? ':' . $device->attribs->firstWhere('attrib_type', 'override_device_ssh_port')->attrib_value : '';
        $ssh_url = LibrenmsConfig::get('gateone.server')
            ? LibrenmsConfig::get('gateone.server') . '?ssh=ssh://' . (LibrenmsConfig::get('gateone.use_librenms_user') ? Auth::user()->username . '@' : '') . $device['hostname'] . '&location=' . $device['hostname']
            : 'ssh://' . $device->hostname . $ssh_port;
        $device_links['ssh'] = [
            'icon' => 'fa-lock',
            'url' => $ssh_url,
            'title' => __('SSH'),
            'external' => true,
        ];

        // Telnet
        $telnet_port = $device->attribs->firstWhere('attrib_type', 'override_device_telnet_port') ? ':' . $device->attribs->firstWhere('attrib_type', 'override_device_telnet_port')->attrib_value : '';
        $device_links['telnet'] = [
            'icon' => 'fa-terminal',
            'url' => 'telnet://' . $device->hostname . $telnet_port,
            'title' => __('Telnet'),
            'external' => true,
        ];

        if (Gate::allows('admin')) {
            $device_links['capture'] = [
                'icon' => 'fa-bug',
                'url' => route('device', [$device->device_id, 'capture']),
                'title' => __('Capture'),
                'external' => false,
            ];
        }

        return $device_links;
    }

    private function editTabExists(string $tab): bool
    {
        Route::has("device.edit.$tab");

        return is_file(base_path("includes/html/pages/device/edit/$tab.inc.php"));
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.device.page-links');
    }
}
