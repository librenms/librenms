namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class CiscoConfigManNotifications implements SnmptrapHandler
{
    /**
     * Handle snmptrap.
     * Data is pre-parsed and delivered as a Trap.
     *
     * @param Device $device
     * @param Trap $trap
     * @return void
     */
    public function handle(Device $device, Trap $trap)
    {
       $trap->log("SNMP TRAP: Configuration Changed")
       if(\LibreNMS\Config::get('oxidized.enabled'))
         $oxidized_api = new \App\ApiClients\Oxidized();
         $oxidized_api->updateNode($hostname, "SNMP Trap")
    }
}
