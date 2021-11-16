<?php
/**
 * RunAlertRulesAction.php
 *
 * Check alert rules for status changes
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
 * @link       http://librenms.org
 *
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Actions\Alerts;

use App\Models\Device;
use LibreNMS\Alert\AlertRules;

class RunAlertRulesAction
{
    /**
     * @var \LibreNMS\Alert\AlertRules
     */
    private $rules;
    /**
     * @var \App\Models\Device
     */
    private $device;

    public function __construct(Device $device, AlertRules $rules)
    {
        $this->rules = $rules;
        $this->device = $device;
    }

    public function execute(): void
    {
        // TODO inline logic
        include_once base_path('includes/common.php');
        include_once base_path('includes/dbFacile.php');
        $this->rules->runRules($this->device->device_id);
    }
}
