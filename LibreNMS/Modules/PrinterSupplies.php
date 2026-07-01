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

use App\Models\Device;
use App\Models\Eventlog;
use App\Models\PrinterSupply;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\PrinterSuppliesContext;
use LibreNMS\OS;
use LibreNMS\Polling\ConnectivityHelper;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Number;
use LibreNMS\Util\StringHelpers;
use SnmpQuery;

class PrinterSupplies implements Module
{
    use SyncsModels;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return [];
    }

    public function shouldDiscover(OS $os, ModuleStatus $status, ConnectivityHelper $connectivity): bool
    {
        return $status->isEnabled() && $connectivity->snmpIsAvailable();
    }

    /**
     * Discover this module. Heavier processes can be run here
     * Run infrequently (default 4 times a day)
     *
     * @param  OS  $os
     */
    public function discover(OS $os): void
    {
        $device = $os->getDevice();
        $device_array = $os->getDeviceArray();
        $contexts = $os instanceof PrinterSuppliesContext ? $os->getPrinterSuppliesContexts() : [''];

        ModuleModelObserver::observe(PrinterSupply::class, __('Printer Supplies'));
        $levels = $this->discoveryLevels($device_array, $contexts);
        $this->syncModelsByGroup($device, 'printerSupplies', $levels, [['supply_type', '!=', 'input']]);
        ModuleModelObserver::done();

        ModuleModelObserver::observe(PrinterSupply::class, __('Tray Paper Level'));
        $papers = $this->discoveryPapers($contexts);
        $this->syncModelsByGroup($device, 'printerSupplies', $papers, ['supply_type' => 'input']);
        ModuleModelObserver::done();
    }

    public function shouldPoll(OS $os, ModuleStatus $status, ConnectivityHelper $connectivity): bool
    {
        return $status->isEnabled() && $connectivity->snmpIsAvailable();
    }

    /**
     * Poll data for this module and update the DB / RRD.
     * Try to keep this efficient and only run if discovery has indicated there is a reason to run.
     * Run frequently (default every 5 minutes)
     *
     * @param  OS  $os
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $device = $os->getDeviceArray();
        $toner_data = $os->getDevice()->printerSupplies;

        if (empty($toner_data)) {
            return; // no data to poll
        }

        $toner_snmp = [];
        $contexts = $os instanceof PrinterSuppliesContext ? $os->getPrinterSuppliesContexts() : [''];
        foreach ($contexts as $context) {
            $toner_snmp = SnmpQuery::device($os->getDevice())
                ->numeric()
                ->context($context)
                ->get($toner_data->pluck('supply_oid')->all())
                ->values();

            if (! empty($toner_snmp)) {
                break;
            }
        }

        foreach ($toner_data as $toner) {
            $raw_toner = $toner_snmp[$toner['supply_oid']] ?? null;
            $tonerperc = self::getTonerLevel($device, $raw_toner, $toner['supply_capacity'] ?? null);
            Log::info('Checking toner ' . $toner['supply_descr'] . "... $tonerperc %");

            $tags = [
                'rrd_def' => RrdDefinition::make()->addDataset('toner', 'GAUGE', 0, 20000),
                'rrd_name' => ['toner', $toner['supply_type'], $toner['supply_index']],
                'rrd_oldname' => ['toner', $toner['supply_descr']],
                'index' => $toner['supply_index'],
            ];
            $datastore->put($device, 'toner', $tags, $tonerperc);

            // Log empty supplies (but only once)
            if ($tonerperc == 0 && $toner['supply_current'] > 0) {
                Eventlog::log(
                    'Toner ' . $toner['supply_descr'] . ' is empty',
                    $os->getDevice(),
                    'toner',
                    Severity::Error,
                    $toner['supply_id']
                );
            }

            // Log toner swap
            if ($tonerperc > $toner['supply_current']) {
                Eventlog::log(
                    'Toner ' . $toner['supply_descr'] . ' was replaced (new level: ' . $tonerperc . '%)',
                    $os->getDevice(),
                    'toner',
                    Severity::Notice,
                    $toner['supply_id']
                );
            }

            $toner->supply_current = $tonerperc;
            $toner->supply_capacity = $toner['supply_capacity'];
            $toner->save();
        }
    }

    public function dataExists(Device $device): bool
    {
        return $device->printerSupplies()->exists();
    }

    /**
     * Remove all DB data for this module.
     * This will be run when the module is disabled.
     */
    public function cleanup(Device $device): int
    {
        return $device->printerSupplies()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        return [
            'printer_supplies' => $device->printerSupplies()->orderBy('supply_oid')->orderBy('supply_index')
                ->get()->map->makeHidden(['device_id', 'supply_id']),
        ];
    }

    private function discoveryLevels(array $device, array $contexts): Collection
    {
        $levels = new Collection();

        $oids = [];
        $context = '';
        foreach ($contexts as $context) {
            $oids = SnmpQuery::hideMib()
                ->enumStrings()
                ->context($context)
                ->walk([
                    'Printer-MIB::prtMarkerSuppliesLevel',
                    'Printer-MIB::prtMarkerSuppliesType',
                    'Printer-MIB::prtMarkerSuppliesMaxCapacity',
                    'Printer-MIB::prtMarkerSuppliesDescription',
                ])->valuesByIndex();

            if (! empty($oids)) {
                break;
            }
        }

        foreach ($oids as $index => $data) {
            if (! isset($data['prtMarkerSuppliesDescription'], $data['prtMarkerSuppliesMaxCapacity'], $data['prtMarkerSuppliesLevel'])) {
                continue;
            }

            $last_index = substr((string) $index, strrpos((string) $index, '.') + 1);

            $descr = $data['prtMarkerSuppliesDescription'];

            // Decode hex-encoded non-ASCII descriptions (e.g. UTF-8 CJK characters from Fujitsu/Ricoh/Kyocera printers)
            // When using -OQUs without -a flag, net-snmp returns non-ASCII strings as hex (e.g. "E9 BB 91 E8 89 B2")
            if (preg_match('/^([A-Fa-f\d]{2} )*[A-Fa-f\d]{2}\s*$/', (string) $descr)) {
                $descr = (string) hex2bin(str_replace([' 00', ' '], '', (string) $descr));
            }
            $raw_capacity = $data['prtMarkerSuppliesMaxCapacity'];
            $raw_toner = $data['prtMarkerSuppliesLevel'];
            $supply_oid = ".1.3.6.1.2.1.43.11.1.1.9.$index";
            $capacity_oid = ".1.3.6.1.2.1.43.11.1.1.8.$index";

            // work around weird HP bug where descriptions are on two lines and the second line is hex
            if (Str::contains($descr, "\n")) {
                $new_descr = '';
                foreach (explode("\n", (string) $descr) as $line) {
                    if (preg_match('/^([A-F\d]{2} )*[A-F\d]{1,2} ?$/', $line)) {
                        $line = StringHelpers::hexToAscii($line, ' ');
                    }
                    $new_descr .= $line;
                }
                $descr = trim($new_descr);
            }

            // Ricoh - TONERCurLevel
            if (empty($raw_toner)) {
                $supply_oid = ".1.3.6.1.4.1.367.3.2.1.2.24.1.1.5.$last_index";
                $raw_toner = SnmpQuery::context($context)->get($supply_oid)->value();
                if ($raw_toner === '' && $device['os'] === 'brother') {
                    // Preserve legacy Brother handling when this vendor fallback OID is absent.
                    $raw_toner = '0';
                }
            }

            // Ricoh - TONERNameLocal
            if (empty($descr)) {
                $descr_oid = ".1.3.6.1.4.1.367.3.2.1.2.24.1.1.3.$last_index";
                $descr = SnmpQuery::context($context)->get($descr_oid)->value();
            }

            // trim part & serial number from devices that include it
            if (Str::contains($descr, ', PN')) {
                $descr = explode(', PN', (string) $descr)[0];
            }

            $capacity = self::getTonerCapacity($raw_capacity);
            $current = self::getTonerLevel($device, $raw_toner, $capacity);

            if (is_numeric($current)) {
                $levels->push(new PrinterSupply([
                    'supply_oid' => $supply_oid,
                    'supply_capacity_oid' => $capacity_oid,
                    'supply_index' => $last_index,
                    'supply_type' => $data['prtMarkerSuppliesType'] ?? 'markerSupply',
                    'supply_descr' => $descr,
                    'supply_capacity' => $capacity,
                    'supply_current' => $current,
                ]));
            }
        }

        return $levels;
    }

    private function discoveryPapers(array $contexts): Collection
    {
        $papers = new Collection();

        $tray_oids = [];
        foreach ($contexts as $context) {
            $tray_oids = SnmpQuery::hideMib()
                ->enumStrings()
                ->context($context)
                ->walk([
                    'Printer-MIB::prtInputName',
                    'Printer-MIB::prtInputCurrentLevel',
                    'Printer-MIB::prtInputMaxCapacity',
                ])->valuesByIndex();

            if (! empty($tray_oids)) {
                break;
            }
        }

        foreach ($tray_oids as $index => $data) {
            if (! isset($data['prtInputName'], $data['prtInputCurrentLevel'], $data['prtInputMaxCapacity'])) {
                continue;
            }

            $last_index = substr((string) $index, strrpos((string) $index, '.') + 1);

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
                $current = Number::calculatePercent($current, $capacity);
            }

            $papers->push(new PrinterSupply([
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
     * @param  array  $device
     * @param  int|string  $raw_value  The value returned from snmp
     * @param  int  $capacity  the normalized capacity
     * @return int|float|bool the toner level as a percentage
     */
    private static function getTonerLevel(array $device, $raw_value, $capacity)
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
            if (! Str::contains($device['hardware'] ?? '', 'MFC-L8850')) {
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

        return Number::calculatePercent($raw_value, $capacity, 0);
    }

    /**
     * @param  int  $raw_capacity  The value return from snmp
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
