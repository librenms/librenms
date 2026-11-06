<?php

/**
 * PortEventsController.php
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
 * @copyright  2026 LibreNMS
 * @author     LibreNMS Contributors
 */

namespace App\Http\Controllers;

use App\Models\Eventlog;
use App\Models\Port;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PortEventsController
{
    public function __invoke(Port $port): View
    {
        // Check access permissions
        Gate::authorize('view', $port);

        $entries = Eventlog::query()
            ->where('device_id', $port->device_id)
            ->where('type', 'interface')
            ->where('reference', $port->port_id)
            ->orderByDesc('datetime')
            ->limit(250)
            ->get();

        return view('port.events', [
            'port' => $port,
            'device' => $port->device,
            'entries' => $entries,
        ]);
    }
}
