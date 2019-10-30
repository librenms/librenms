<?php
/**
 * Dispatcher.php
 *
 * Creates the correct handler for the trap and then sends it the trap.
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

namespace LibreNMS\Snmptrap;

use LibreNMS\Config;
use LibreNMS\Snmptrap\Handlers\Fallback;
use LibreNMS\Alert\AlertRules;
use Log;

class Dispatcher
{
    /**
     * Instantiate the correct handler for this trap and call it's handle method
     *
     */
    public static function handle(Trap $trap)
    {
        if (empty($trap->getDevice())) {
            Log::warning("Could not find device for trap", ['trap_text' => $trap->getRaw()]);
            return false;
        }

        // note, this doesn't clear the resolved SnpmtrapHandler so only one per run
        /** @var \LibreNMS\Interfaces\SnmptrapHandler $handler */
        $handler = app(\LibreNMS\Interfaces\SnmptrapHandler::class, [$trap->getTrapOid()]);
        $handler->handle($trap->getDevice(), $trap);

        // log an event if appropriate
        $fallback = $handler instanceof Fallback;
        $logging = Config::get('snmptraps.eventlog', 'unhandled');
        if ($logging == 'all' || ($fallback && $logging == 'unhandled')) {
            Log::event("SNMP trap received: " . $trap->getTrapOid(), $trap->getDevice(), 'trap');
        } else {
            $rules = new AlertRules;
            $rules->runRules($trap->getDevice()->device_id);
        }

        return !$fallback;
    }
}
