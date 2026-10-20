<?php

/**
 * AddScannedHost.php
 *
 * Add a host found by a network scan to LibreNMS.
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Jobs;

use App\Actions\Device\AddFoundDevice;
use App\Actions\Device\AddFoundDeviceResult;
use App\Actions\Device\BuildDefaultPollingMethods;
use App\Models\Device;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use LibreNMS\Enum\DeviceAddOutcome;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretMode;
use LibreNMS\Util\Dns;

class AddScannedHost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 1; // a scan is repeated on its own schedule, don't retry

    /**
     * @param  int|null  $snmpSecretId  the secret the host replied to, null to add the host as ping only
     * @param  bool  $pingable  the host replied to ping, it is not pinged again. With SNMP, a host that did not reply is added without ICMP
     */
    public function __construct(
        public string $ip,
        public ?int $snmpSecretId,
        public bool $pingable,
        public int $pollerGroup = 0,
        public bool $dnsOnly = false,
    ) {
    }

    public function displayName(): string
    {
        return "AddScannedHost:$this->ip";
    }

    /**
     * The result is returned when run in the current process with Bus::dispatchNow().
     */
    public function handle(Dns $dns, BuildDefaultPollingMethods $builder, AddFoundDevice $addFoundDevice): AddFoundDeviceResult
    {
        $hostname = $dns->resolveHostname($this->ip);

        if ($this->dnsOnly && ! $hostname) {
            return new AddFoundDeviceResult(DeviceAddOutcome::NoDns);
        }

        $device = new Device([
            'hostname' => $hostname ?: $this->ip,
            'poller_group' => $this->pollerGroup,
        ]);

        $pollingMethods = $builder->execute($device, ['methods' => $this->snmpSecretId === null
            ? [PollingMethodType::Icmp->value => ['active' => true]]
            : [
                PollingMethodType::Icmp->value => ['active' => $this->pingable],
                PollingMethodType::Snmp->value => ['active' => true, 'secret_mode' => SecretMode::Existing->value, 'secret_id' => $this->snmpSecretId],
            ],
        ]);

        return $addFoundDevice->execute($device, $pollingMethods, $this->pingable ? [PollingMethodType::Icmp] : []);
    }
}
