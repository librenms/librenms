<?php
/**
 * PrinterSupplies.php
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
 */

namespace LibreNMS\Modules;

use App\Models\PrinterSupply;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Enum\Alert;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;
use Log;

class PrinterSupplies implements Module
{
    use SyncsModels;

    /**
     * Discover this module. Heavier processes can be run here
     * Run infrequently (default 4 times a day)
     *
     * @param OS $os
     */
    public function discover(OS $os)
    {
        $device = $os->getDeviceArray();

        $data = collect()
            ->concat($this->discoveryLevels($device))
            ->concat($this->discoveryPapers($device));

        ModuleModelObserver::observe(PrinterSupply::class);
        $this->syncModels($os->getDevice(), 'printerSupplies', $data);
    }

    /**
     * Poll data for this module and update the DB / RRD.
     * Try to keep this efficient and only run if discovery has indicated there is a reason to run.
     * Run frequently (default every 5 minutes)
     *
     * @param OS $os
     */
    public function poll(OS $os)
    {
        $device = $os->getDeviceArray();
        $toner_data = $os->getDevice()->printerSupplies;

        $toner_snmp = snmp_get_multi_oid($device, $toner_data->pluck('supply_oid')->toArray());

        foreach ($toner_data as $toner) {
            echo 'Checking toner ' . $toner['supply_descr'] . '... ';

            $raw_toner = $toner_snmp[$toner['supply_oid']];
            $tonerperc = self::getTonerLevel($device, $raw_toner, $toner['supply_capacity']);
            echo $tonerperc . " %\n";

            $tags = [
                'rrd_def'     => RrdDefinition::make()->addDataset('toner', 'GAUGE', 0, 20000),
                'rrd_name'    => ['toner', $toner['supply_type'], $toner['supply_index']],
                'rrd_oldname' => ['toner', $toner['supply_descr']],
                'index'       => $toner['supply_index'],
            ];
            data_update($device, 'toner', $tags, $tonerperc);

            // Log empty supplies (but only once)
            if ($tonerperc == 0 && $toner['supply_current'] > 0) {
                Log::event(
                    'Toner ' . $toner['supply_descr'] . ' is empty',
                    $os->getDevice(),
                    'toner',
                    Alert::ERROR,
                    $toner['supply_id']
                );
            }

            // Log toner swap
            if ($tonerperc > $toner['supply_current']) {
                Log::event(
                    'Toner ' . $toner['supply_descr'] . ' was replaced (new level: ' . $tonerperc . '%)',
                    $os->getDevice(),
                    'toner',
                    Alert::NOTICE,
                    $toner['supply_id']
                 );
            }

            $toner->supply_current = $tonerperc;
            $toner->supply_capacity = $toner['supply_capacity'];
            $toner->save();
        }
    }

    /**
     * Remove all DB data for this module.
     * This will be run when the module is disabled.
     *
     * @param OS $os
     */
    public function cleanup(OS $os)
    {
        $os->getDevice()->printerSupplies()->delete();
    }

    private function discoveryLevels($device): Collection
    {
        $levels = collect();

        $oids = snmpwalk_cache_oid($device, 'prtMarkerSuppliesLevel', [], 'Printer-MIB');
        if (! empty($oids)) {
            $oids = snmpwalk_cache_oid($device, 'prtMarkerSuppliesType', $oids, 'Printer-MIB');
            $oids = snmpwalk_cache_oid($device, 'prtMarkerSuppliesMaxCapacity', $oids, 'Printer-MIB');
            $oids = snmpwalk_cache_oid($device, 'prtMarkerSuppliesDescription', $oids, 'Printer-MIB', null, '-OQUsa');
        }

        foreach ($oids as $index => $data) {
            $last_index = substr($index, strrpos($index, '.') + 1);

            $descr = $data['prtMarkerSuppliesDescription'];
            $raw_capacity = $data['prtMarkerSuppliesMaxCapacity'];
            $raw_toner = $data['prtMarkerSuppliesLevel'];
            $supply_oid = ".1.3.6.1.2.1.43.11.1.1.9.$index";
            $capacity_oid = ".1.3.6.1.2.1.43.11.1.1.8.$index";

            // work around weird HP bug where descriptions are on two lines and the second line is hex
            if (Str::contains($descr, "\n")) {
                $new_descr = '';
                foreach (explode("\n", $descr) as $line) {
                    if (preg_match('/^([A-F\d]{2} )*[A-F\d]{1,2} ?$/', $line)) {
                        $line = snmp_hexstring($line);
                    }
                    $new_descr .= $line;
                }
                $descr = trim($new_descr);
            }

            // Ricoh - TONERCurLevel
            if (empty($raw_toner)) {
                $supply_oid = ".1.3.6.1.4.1.367.3.2.1.2.24.1.1.5.$last_index";
                $raw_toner = snmp_get($device, $supply_oid, '-Oqv');
            }

            // Ricoh - TONERNameLocal
            if (empty($descr)) {
                $descr_oid = ".1.3.6.1.4.1.367.3.2.1.2.24.1.1.3.$last_index";
                $descr = snmp_get($device, $descr_oid, '-Oqva');
            }

            // trim part & serial number from devices that include it
            if (Str::contains($descr, ', PN')) {
                $descr = explode(', PN', $descr)[0];
            }

            $capacity = self::getTonerCapacity($raw_capacity);
            $current = self::getTonerLevel($device, $raw_toner, $capacity);

            if (is_numeric($current)) {
                $levels->push(new PrinterSupply([
                    'device_id' => $device['device_id'],
                    'supply_oid' => $supply_oid,
                    'supply_capacity_oid' => $capacity_oid,
                    'supply_index' => $last_index,
                    'supply_type' => $data['prtMarkerSuppliesType'] ?: 'markerSupply',
                    'supply_descr' => $descr,
                    'supply_capacity' => $capacity,
                    'supply_current' => $current,
                ]));
            }
        }

        return $levels;
    }

    private function discoveryPapers($device): Collection
    {
        echo 'Tray Paper Level: ';
        $papers = collect();

        $tray_oids = snmpwalk_cache_oid($device, 'prtInputName', [], 'Printer-MIB');
        if (! empty($tray_oids)) {
            $tray_oids = snmpwalk_cache_oid($device, 'prtInputCurrentLevel', $tray_oids, 'Printer-MIB');
            $tray_oids = snmpwalk_cache_oid($device, 'prtInputMaxCapacity', $tray_oids, 'Printer-MIB');
        }

        foreach ($tray_oids as $index => $data) {
            $last_index = substr($index, strrpos($index, '.') + 1);

            $capacity = $data['prtInputMaxCapacity'];
            $current = $data['prtInputCurrentLevel'];

            if (! is_numeric($current) || $current == -2) {
                // capacity unsupported
                d_echo('Input Capacity unsupported', 'X');
                continue;
            } elseif ($current == -3) {
                // at least one piece of paper in tray
                $current = 50;
            } else {
                $current = $current / $capacity * 100;
            }

            $papers->push(new PrinterSupply([
                'device_id' => $device['device_id'],
                'supply_oid' => ".1.3.6.1.2.1.43.8.2.1.10.$index",
                'supply_capacity_oid' => ".1.3.6.1.2.1.43.8.2.1.9.$index",
                'supply_index' => $last_index,
                'supply_type' => 'input',
                'supply_descr' => $data['prtInputName'],
                'supply_capacity' => $capacity,
                'supply_current' => $current,
            ]));
        }

        return $papers;
    }

    /**
     * @param array $device
     * @param int|string $raw_value The value returned from snmp
     * @param int $capacity the normalized capacity
     * @return int|float|bool the toner level as a percentage
     */
    private static function getTonerLevel($device, $raw_value, $capacity)
    {
        // -3 means some toner is left
        if ($raw_value == '-3') {
            return 50;
        }

        // -2 means unknown
        if ($raw_value == '-2') {
            return false;
        }

        // -1 mean no restrictions
        if ($raw_value == '-1') {
            return 0;  // FIXME: is 0 what we should return?
        }

        // Non-standard snmp values
        if ($device['os'] == 'ricoh' || $device['os'] == 'nrg' || $device['os'] == 'lanier') {
            if ($raw_value == '-100') {
                return 0;
            }
        } elseif ($device['os'] == 'brother') {
            if (! Str::contains($device['hardware'], 'MFC-L8850')) {
                switch ($raw_value) {
                    case '0':
                        return 100;
                    case '1':
                        return 5;
                    case '2':
                        return 0;
                    case '3':
                        return 1;
                }
            }
        }

        return round($raw_value / $capacity * 100);
    }

    /**
     * @param int $raw_capacity The value return from snmp
     * @return int normalized capacity value
     */
    private static function getTonerCapacity($raw_capacity)
    {
        // unknown or unrestricted capacity, assume 100
        if (empty($raw_capacity) || $raw_capacity < 0) {
            return 100;
        }

        return $raw_capacity;
    }
}
