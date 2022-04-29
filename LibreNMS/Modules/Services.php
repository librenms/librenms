<?php
/**
 * Services.php
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use App\Http\Controllers\ServiceTemplateController;
use App\Models\Device;
use App\Models\Service;
use App\Observers\ModuleModelObserver;
use LibreNMS\Config;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Services\ServiceCheckResponse;
use Log;
use Symfony\Component\Process\Process;

class Services implements Module
{

    public function discover(OS $os)
    {
        if (Config::get('discover_services_templates')) {
            (new ServiceTemplateController())->applyAll(); // FIXME applyAll() should not be on a controller
        }

        if (Config::get('discover_services')) {
            // FIXME: use /etc/services?
            $known_services = [
                22 => 'ssh',
                25 => 'smtp',
                53 => 'dns',
                80 => 'http',
                110 => 'pop',
                143 => 'imap',
            ];

            ModuleModelObserver::observe(Service::class);
            // Services
            $services = \SnmpQuery::enumStrings()->walk('TCP-MIB::tcpConnState')->mapTable(function ($data, $localAddress, $localPort) use ($os, $known_services) {
                if ($data['TCP-MIB::tcpConnState'] == 'listen' && $localAddress == '0.0.0.0') {
                    if (isset($known_services[$localPort])) {
                        $service = $known_services[$localPort];
                        $new_service = Service::firstOrNew([
                            'device_id' => $os->getDeviceId(),
                            'service_ip' => $os->getDevice()->overwrite_ip ?: $os->getDevice()->hostname,
                            'service_type' => $service,
                        ], [
                            'service_name' => "AUTO: $service",
                            'service_desc' => "$service Monitoring (Auto Discovered)",
                            'service_changed' => time(),
                            'service_param' => '',
                            'service_ignore' => 0,
                            'service_disabled' => 0,
                            'service_status' => 3,
                            'service_message' => 'Service not yet checked',
                            'service_ds' => '{}',
                            'service_template_id' => 0
                        ]);

                        if (! $new_service->exists && $new_service->save()) {
                            Log::event("Autodiscovered service: type $service", $os->getDevice(), 'service');
                        }
                    }
                }
            });
        }
    }

    public function poll(OS $os)
    {
        $count = 0;
        $device = $os->getDevice();

        /** @var Service $service */
        foreach ($device->services()->where('service_disabled', 0)->get() as $service) {
            if ($this->canSkip($device, $service)) {
                $device_name = $device->displayName();
                Log::debug("Skipping service check $service->service_id because device $device_name is down due to icmp.");
                Log::event("Service check - $service->service_desc ($service->service_id) - Skipping service check because device $device_name is down due to icmp", $device, 'service', 4, $service->service_id);
                continue;
            }

            Log::info("Nagios Service $service->service_type ($service->service_id)");
            $response = $this->checkService($device, $service);
            $service->service_message = $response->message;
            $service->service_status = $response->result;
            Log::debug("Service Response: $response->message");

            // If we have performance data we will store it.
            if (! empty ($response->metrics)) {
                $service->service_ds = array_map(function ($metric) { return $metric['uom']; }, $response->metrics);
                Log::debug('Service DS: ' . json_encode($service->service_ds));

                $rrd_def = new RrdDefinition();
                $fields = [];
                foreach ($response->metrics as $key => $data) {
                    // c = counter type (exclude uptime)
                    $ds_type = ($data['uom'] == 'c') && ! (preg_match('/[Uu]ptime/', $key)) ? 'COUNTER' : 'GAUGE';
                    $rrd_def->addDataset($key, $ds_type, 0);

                    // prep update data
                    $fields[$key] = $data['value'];
                }

                app('Datastore')->put($os->getDeviceArray(), 'services', [
                    'service_id' => $service->service_id,
                    'rrd_name' => ['services', $service->service_id],
                    'rrd_def' => $rrd_def,
                ], $fields);
            }

            $service->save(); // save if changed
            $count++;
        }

        return $count;
    }

    public function cleanup(OS $os)
    {
        $os->getDevice()->services()->delete();
    }

    public function checkService(Device $device, Service $service): ServiceCheckResponse
    {
        $command = \LibreNMS\Services::makeCheck($service)->buildCommand($device);
        $process = new Process($command, null, ['LC_NUMERIC' => 'C']);
        $process->run();

        return new ServiceCheckResponse($process->getOutput(), $process->getExitCode());
    }

    private function canSkip(Device $device, Service $service): bool
    {
        if ($device->status == 0) {
            if ($service->service_ip == $device->ip || $service->service_ip == $device->hostname) {
                if ($device->status_reason !== 'snmp' && ! $device->getAttrib('override_icmp_disable')) {
                    return true;
                }
            }
        }

        return false;
    }
}
