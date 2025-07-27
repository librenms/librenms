<?php
/**
 * Page.php
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

use App\Models\Device;
use App\Models\Vminfo;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use LibreNMS\Util\Graph;

class Page extends Component
{
    public string $alertClass;
    public ?int $parentDeviceId;


    public function __construct(
        public readonly Device $device,
        public readonly array $dropdownLinks = [],
    )
    {
        $this->alertClass = $device->disabled ? 'alert-info' : ($device->status ? '' : 'alert-danger');
        $this->parentDeviceId = Vminfo::guessFromDevice($device)->value('device_id');
    }

    public function overviewGraphs(): array
    {
        $graph_array = [
            'width' => 150,
            'height' => 45,
            'device' => $this->device->device_id,
            'type' => 'device_bits',
            'from' => '-1d',
            'legend' => 'no',
            'bg' => 'FFFFFF00',
        ];

        $graphs = [];
        foreach (Graph::getOverviewGraphsForDevice($this->device) as $graph) {
            $graph_array['type'] = $graph['graph'];
            $graph_array['popup_title'] = __($graph['text']);
            $graphs[] = $graph_array;
        }

        return $graphs;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.device.page');
    }

}
