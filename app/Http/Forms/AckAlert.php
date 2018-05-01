<?php
/**
 * AckAlert.php
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
 * @copyright  2018 Neil Lathwood
 * @copyright  2018 Tony Murray
 * @author     Neil Lathwood <gh+n@laf.io>
 */

namespace App\Http\Forms;

use App\Models\Alert;
use App\Models\Eventlog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use LibreNMS\Authentication\Auth;
use LibreNMS\Config;

class AckAlert extends BaseForm
{
    protected $validation_rules = [
        'alert_id' => 'required|integer',
        'state' => 'required|integer',
    ];

    protected $validation_messages = [
        'alert_id' => 'No alert selected',
        'state' => 'No state passed',
    ];

    /**
     * @param Request $request
     * @return array
     */
    public function handleRequest(Request $request)
    {
        $alert = Alert::findOrFail($request->get('alert_id'));

        $state = $request->get('state');
        if ($state == 2) {
            $alert->state = 1;
            $state_descr = 'UnAck';
            $alert->open = 1;
        } elseif ($state >= 1) {
            $alert->state = 2;
            $state_descr = 'Ack';
            $alert->open = 1;
        }

        $username = Auth::user()->username;

        // add ack message to the alert
        if (!empty($alert->note)) {
            $alert->note .= PHP_EOL;
        }
        $alert->note .= Carbon::now()->format(Config::get('dateformat.long'));
        $alert->note .= " - $state_descr ($username) " . $request->get('ack_msg');

        if ($alert->save()) {
            Eventlog::event("$username {$state_descr}nowledged alert {$alert->rule->name}", $alert->device_id, 'alert', 2, $alert->id);
            return $this->formatResponse(true, 'Alert acknowledged status changed.');
        } else {
            return $this->formatResponse(false, 'Alert has not been acknowledged.');
        }
    }
}
