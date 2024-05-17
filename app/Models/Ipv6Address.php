<?php
/**
 * Ipv6Address.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

namespace App\Models;

use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\Models\Keyable;
use LibreNMS\Util\IPv6;
use Log;

class Ipv6Address extends PortRelatedModel implements Keyable
{
    public $timestamps = false;
    protected $primaryKey = 'ipv6_address_id';
    protected $fillable = [
        'device_id',
        'ipv6_address',
        'ipv6_compressed',
        'ipv6_prefixlen',
        'ipv6_origin',
        'ipv6_network',
        'port_id',
        'context_name',
    ];

    public function getCompositeKey()
    {
        return $this->port_id;
    }

    public static function processIpv6(int $device_id, array $collectedData)
    {
        $validAddresses = [];
        foreach ($collectedData as $row) {
            if (IPv6::isValid($row['address'], true)) {
                $ipv6 = new IPv6($row['address']);
                $ipv6_network = $ipv6->getNetwork($row['prefixlen']);
                $ipv6_compressed = $ipv6->compressed();
                $ipv6_uncompressed = $ipv6->uncompressed();
                Log::debug('IPv6 -> Processing ' . $ipv6_compressed . ' | ' . $ipv6_network);

                $port_id = Port::where([
                    ['device_id', $device_id],
                    ['ifIndex', $row['ifIndex']],
                ])->value('port_id');

                if (! empty($port_id) && $row['prefixlen'] > '0' && $row['prefixlen'] < '129' && ! empty($row['origin'])) {
                    Log::debug('IPV6 -> Found port id ' . $port_id);

                    $dbIpv6Addr = Ipv6Address::updateOrCreate([
                        'device_id' => $device_id,
                        'ipv6_address' => $ipv6_uncompressed,
                        'ipv6_prefixlen' => $row['prefixlen'],
                        'port_id' => $port_id,
                    ], [
                        'ipv6_compressed' => $ipv6_compressed,
                        'ipv6_origin' => $row['origin'],
                        'ipv6_network' => $ipv6_network,
                        'context_name' => $row['context_name'],
                    ]);

                    if (! $dbIpv6Addr->wasRecentlyCreated && $dbIpv6Addr->wasChanged()) {
                        Eventlog::log('IPv6 address ' . $ipv6_compressed . '/' . $row['prefixlen'] . ' changed ', $device_id, 'ipv6', Severity::Warning, $port_id);
                        echo 'U';
                    }
                    if ($dbIpv6Addr->wasRecentlyCreated) {
                        Eventlog::log('IPv6 address ' . $ipv6_compressed . '/' . $row['prefixlen'] . ' created ', $device_id, 'ipv6', Severity::Notice, $port_id);
                        echo '+';
                    }

                    $validAddresses[$ipv6_compressed][$row['prefixlen']][$ipv6_network][$port_id] = true;
                }
            }
        }

        Log::debug('IPv6 -> Cleanup');
        $fromDb = Ipv6Address::where('device_id', $device_id)->orWhere('device_id', null)
            ->select('ipv6_address_id', 'ipv6_compressed', 'ipv6_prefixlen', 'ipv6_network', 'port_id')
            ->get()->toArray();

        foreach ($fromDb as $row) {
            if (empty($validAddresses[$row['ipv6_compressed']][$row['ipv6_prefixlen']][$row['ipv6_network']][$row['port_id']])) {
                Ipv6Address::where('ipv6_address_id', $row['ipv6_address_id'])->delete();
                Eventlog::log('IPv6 address: ' . $row['ipv6_compressed'] . '/' . $row['ipv6_prefixlen'] . ' deleted', $device_id, 'ipv6', Severity::Warning, $row['port_id']);
                echo '-';
            }
        }
    }
}
