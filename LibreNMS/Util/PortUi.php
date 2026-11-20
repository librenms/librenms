<?php

namespace LibreNMS\Util;

use App\Models\Device;
use App\Models\EntPhysical;
use App\Models\Port;
use Illuminate\Support\Collection;

class PortUi
{
    /**
     * Build the Port UI visual layout data for the device.
     *
     * @param Device $device
     * @return array<string, mixed>|null
     */
    public static function build(Device $device): ?array
    {
        // 1. Load ports
        $ports = $device->ports()
            ->isNotDeleted()
            ->orderBy('ifIndex')
            ->get();

        if ($ports->isEmpty()) {
            return null;
        }

        // Index ports by various name formats for flexible matching
        $indexedPorts = self::indexPorts($ports);

        // 2. Load entPhysical mapping
        $entPhysicalData = self::loadEntPhysicalData($device);

        // 3. Check for device-specific JSON template
        $template = self::findTemplate($device);
        if ($template && ! empty($template['front'])) {
            $transform = self::generateTransformFromTemplate($template);
        } else {
            // Auto-detect layout from physical ports
            $transform = self::transformPortsAuto($ports, 2, $entPhysicalData['map']);
        }

        if (empty($transform)) {
            return null;
        }

        // 4. Determine switches/stack members
        $switches = self::findSwitchesRange($indexedPorts, $entPhysicalData['switches']);

        // 5. Structure the visual layout
        return self::buildVisualGrid($switches, $transform, $indexedPorts, $entPhysicalData['map']);
    }

    /**
     * Locate and decode the device JSON template if present.
     *
     * @param Device $device
     * @return array<string, mixed>|null
     */
    public static function findTemplate(Device $device): ?array
    {
        $hardware = str_replace([' ', '/'], '_', strtolower($device->hardware ?? ''));
        $brand = strtolower(substr(basename($device->icon ?? ''), 0, -4));

        if (! $hardware || ! $brand) {
            return null;
        }

        $baseDir = function_exists('resource_path') && app()->has('path.resources')
            ? resource_path('views')
            : dirname(__DIR__, 2) . '/resources/views';

        $filePath = "{$baseDir}/device/portui/{$brand}/{$hardware}.json";
        if (! file_exists($filePath)) {
            return null;
        }

        $content = file_get_contents($filePath);
        if (! $content) {
            return null;
        }

        $json = json_decode($content, true);
        return is_array($json) ? $json : null;
    }

    /**
     * Generate port transformation array from JSON template definitions.
     *
     * @param array<string, mixed> $json
     * @return array<int, array<int, array<int, array<int, array<string, string>>>>>
     */
    public static function generateTransformFromTemplate(array $json): array
    {
        $result = [];
        $module = 0;

        if (isset($json['front']) && is_array($json['front'])) {
            foreach ($json['front'] as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $count = (int) ($item['count'] ?? 0);
                $height = max(1, (int) ($item['height'] ?? 2));
                $block = (int) ($item['block'] ?? 0);
                $start = (string) ($item['start'] ?? '');
                $type = (string) ($item['type'] ?? 'cu');

                for ($i = 0; $i < $count; $i++) {
                    $column = (int) floor($i / $height);
                    $parts = array_reverse(explode('/', $start));
                    $ifName = $start;

                    if (count($parts) > 1) {
                        if (is_numeric($parts[0])) {
                            $parts[0] = (string) ((int) $parts[0] + $i);
                        }
                        $ifName = implode('/', array_reverse($parts));
                    } else {
                        if (preg_match('/^([a-z-_ ]+?)([0-9]+)$/i', $parts[0], $matches)) {
                            $ifName = $matches[1] . ((int) $matches[2] + $i);
                        }
                    }

                    $result[$module][$block][$column][] = [$ifName => $type];
                }
            }
        }

        return $result;
    }

    /**
     * Auto-detect switch, module, block, and column layout when no template exists.
     *
     * @param Collection<int, Port> $ports
     * @param int $rowHeight
     * @param array<string, mixed> $entPhysicalMap
     * @return array<int, array<int, array<int, array<int, array<string, string>>>>>
     */
    public static function transformPortsAuto(Collection $ports, int $rowHeight, array $entPhysicalMap): array
    {
        $result = [];
        $i = 0;
        $block = 0;
        $module = 0;
        $switch = 1;

        $prevType = null;
        $prevPrefix = null;
        $prevPartsCount = null;
        $prevModule = null;
        $prevSwitch = null;

        foreach ($ports as $port) {
            $ifName = $port->ifName;
            $ifType = $port->ifType;
            $ifDescr = $port->ifDescr;

            if (! $ifName) {
                continue;
            }

            // Only physical ethernet ports
            if ($ifType && $ifType !== 'ethernetCsmacd') {
                continue;
            }

            if (preg_match('/^(lo|br|wifi|[.][0-9]+|ovpn|tun|tap|sit|enc|vlan|null)/i', $ifName)) {
                continue;
            }

            $bumpBlock = false;
            $types = self::findEntPhysicalPortType($entPhysicalMap, $ifDescr ?? $ifName);
            $currentType = $types[0] ?? 'cu';

            if ($prevType !== null && $prevType !== $currentType) {
                $bumpBlock = true;
            }

            preg_match('/^([a-z-_ ]+)/i', $ifName, $prefixMatch);
            $prefix = $prefixMatch[1] ?? '';
            if ($prevPrefix !== null && $prevPrefix !== $prefix) {
                $bumpBlock = true;
            }

            $parts = explode('/', $ifName);
            $partsCount = count($parts);
            if ($prevPartsCount !== null && $prevPartsCount !== $partsCount) {
                $bumpBlock = true;
            }

            if ($bumpBlock) {
                $block++;
                $i = 0;
            }

            if ($prevModule !== null && $prevModule !== $module) {
                $block = 0;
            }

            if ($prevSwitch !== null && $prevSwitch !== $switch) {
                $block = 0;
                // If more than 1 switch, assume stack and preserve layout
                return $result;
            }

            switch ($partsCount) {
                case 3:
                    if (preg_match('/([0-9]+)/', $parts[0], $sm)) {
                        $switch = (int) $sm[1];
                    }
                    $module = (int) $parts[1];
                    $height = $rowHeight;
                    break;
                case 2:
                    $module = (int) $parts[0];
                    $height = $rowHeight;
                    break;
                default:
                    $module = 0;
                    $height = 1;
                    break;
            }

            $col = (int) floor($i / max(1, $height));
            $result[$module][$block][$col][] = [$ifName => $currentType];

            $prevModule = $module;
            $prevSwitch = $switch;
            $prevPrefix = $prefix;
            $prevPartsCount = $partsCount;
            $prevType = $currentType;
            $i++;
        }

        return $result;
    }

    /**
     * Load entPhysical entities and index them by name and chassis switches.
     *
     * @param Device $device
     * @return array{map: array<string, mixed>, switches: array<int, string>}
     */
    public static function loadEntPhysicalData(Device $device): array
    {
        $entities = $device->entityPhysical()
            ->orderBy('entPhysicalContainedIn')
            ->orderBy('entPhysicalIndex')
            ->get();

        $map = [];
        $switches = [];
        $switchIdx = 1;

        foreach ($entities as $ent) {
            $class = $ent->entPhysicalClass;
            $name = $ent->entPhysicalName;

            if ($name && in_array($class, ['port', 'container'], true)) {
                $map[$name] = [
                    'entPhysicalName' => $name,
                    'entPhysicalClass' => $class,
                    'entPhysicalVendorType' => $ent->entPhysicalVendorType,
                    'entPhysicalModelName' => $ent->entPhysicalModelName,
                    'entPhysicalSerialNum' => $ent->entPhysicalSerialNum,
                ];
            }

            if ($class === 'chassis' && $ent->entPhysicalSerialNum) {
                $switches[$switchIdx++] = $ent->entPhysicalSerialNum;
            }
        }

        return ['map' => $map, 'switches' => $switches];
    }

    /**
     * Determine port physical media type (cu, sfp, qsfp, mmsr) from entPhysical data.
     *
     * @param array<string, mixed> $entPhysicalMap
     * @param string $ifDescr
     * @return array<int, string>
     */
    public static function findEntPhysicalPortType(array $entPhysicalMap, string $ifDescr): array
    {
        $result = [];
        $candidates = ["{$ifDescr} Container", $ifDescr];

        foreach ($candidates as $name) {
            if (isset($entPhysicalMap[$name]['entPhysicalVendorType'])) {
                $vt = (string) $entPhysicalMap[$name]['entPhysicalVendorType'];
                if (preg_match('/(BaseT|WiredSwitch[0-9]Gb)/i', $vt)) {
                    $result[] = 'cu';
                } elseif (preg_match('/(qsfp|WiredSwitch40Gb)/i', $vt)) {
                    $result[] = 'qsfp';
                } elseif (preg_match('/(sfp|WiredSwitch[125]0Gb)/i', $vt)) {
                    $result[] = 'sfp';
                } elseif (preg_match('/(basesx)/i', $vt)) {
                    $result[] = 'mmsr';
                }
            }
        }

        return $result;
    }

    /**
     * Find switch stack range from port names and chassis data.
     *
     * @param array<string, Port> $indexedPorts
     * @param array<int, string> $chassisSwitches
     * @return array<int, string|null>
     */
    public static function findSwitchesRange(array $indexedPorts, array $chassisSwitches): array
    {
        $switches = [];

        foreach ($indexedPorts as $ifName => $port) {
            $parts = explode('/', (string) $ifName);
            if (count($parts) === 3 && preg_match('/([0-9]+)/', $parts[0], $m)) {
                $switches[(int) $m[1]] = $chassisSwitches[(int) $m[1]] ?? null;
            }
        }

        if (empty($switches)) {
            $switches[1] = $chassisSwitches[1] ?? null;
        }

        ksort($switches);
        return $switches;
    }

    /**
     * Check if columns contain ports matching the given switch stack ID.
     *
     * @param array<int, array<int, array<string, string>>> $columns
     * @param int $switch
     * @return bool
     */
    public static function filterPortsForSwitch(array $columns, int $switch): bool
    {
        foreach ($columns as $ports) {
            foreach ($ports as $portEntry) {
                foreach ($portEntry as $portName => $type) {
                    $parts = explode('/', (string) $portName);
                    if (count($parts) === 3 && preg_match('/([0-9]+)/', $parts[0], $m)) {
                        if ((int) $m[1] !== $switch) {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Index ports for flexible lookup by exact ifName, ifDescr, and short variants.
     *
     * @param Collection<int, Port> $ports
     * @return array<string, Port>
     */
    public static function indexPorts(Collection $ports): array
    {
        $indexed = [];

        foreach ($ports as $port) {
            if ($port->ifName) {
                $indexed[$port->ifName] = $port;
                $indexed[strtolower($port->ifName)] = $port;
                $indexed[self::normalizePortName($port->ifName)] = $port;
            }
            if ($port->ifDescr) {
                $indexed[$port->ifDescr] = $port;
                $indexed[strtolower($port->ifDescr)] = $port;
                $indexed[self::normalizePortName($port->ifDescr)] = $port;
            }
        }

        return $indexed;
    }

    /**
     * Normalize port names for matching (e.g. GigabitEthernet1/0/1 <-> Gi1/0/1).
     *
     * @param string $name
     * @return string
     */
    public static function normalizePortName(string $name): string
    {
        $replacements = [
            'TwentyFiveGigE' => 'twe',
            'FortyGigabitEthernet' => 'fo',
            'HundredGigE' => 'hu',
            'TenGigabitEthernet' => 'te',
            'GigabitEthernet' => 'gi',
            'FastEthernet' => 'fa',
            'Ethernet' => 'eth',
        ];

        return strtolower(str_replace(array_keys($replacements), array_values($replacements), $name));
    }

    /**
     * Build the multi-level grid array ready for Blade rendering.
     *
     * @param array<int, string|null> $switches
     * @param array<int, array<int, array<int, array<int, array<string, string>>>>> $transform
     * @param array<string, Port> $indexedPorts
     * @param array<string, mixed> $entPhysicalMap
     * @return array<string, mixed>
     */
    public static function buildVisualGrid(array $switches, array $transform, array $indexedPorts, array $entPhysicalMap): array
    {
        $gridSwitches = [];

        foreach ($switches as $switchId => $serial) {
            $gridModules = [];

            foreach ($transform as $moduleId => $blocks) {
                $gridBlocks = [];

                foreach ($blocks as $blockId => $columns) {
                    if (! self::filterPortsForSwitch($columns, $switchId)) {
                        continue;
                    }

                    $rowCount = 0;
                    foreach ($columns as $colPorts) {
                        $rowCount = max($rowCount, count($colPorts));
                    }

                    $rows = [];
                    for ($r = 0; $r < $rowCount; $r++) {
                        $rows[$r] = [];
                        foreach ($columns as $c => $colPorts) {
                            $portEntry = $colPorts[$r] ?? null;
                            if ($portEntry) {
                                foreach ($portEntry as $portName => $phyType) {
                                    $port = $indexedPorts[$portName]
                                        ?? $indexedPorts[strtolower($portName)]
                                        ?? $indexedPorts[self::normalizePortName($portName)]
                                        ?? null;

                                    $portParts = array_reverse(explode('/', (string) $portName));

                                    // Filter stack switch mismatch
                                    if (count($portParts) === 3 && preg_match('/([0-9]+)/', $portParts[2], $sm)) {
                                        if ((int) $sm[1] !== $switchId) {
                                            $rows[$r][] = null;
                                            continue 2;
                                        }
                                    }

                                    $adminStatus = match ($port?->ifAdminStatus) {
                                        'up' => 'up',
                                        'down' => 'down',
                                        default => 'disabled',
                                    };
                                    $operStatus = ($port?->ifOperStatus === 'up') ? 'up' : 'down';
                                    $vlan = $port?->ifVlan ?: ($port?->ifTrunk ?: 'Unknown');
                                    $speed = $port?->ifSpeed ? \LibreNMS\Util\Number::formatSi($port->ifSpeed, 0, 0, 'bps') : null;

                                    $rows[$r][] = [
                                        'port_id' => $port?->port_id,
                                        'ifName' => $portName,
                                        'ifDescr' => $port?->ifDescr ?? $portName,
                                        'label' => $portParts[0],
                                        'type' => $phyType ?: 'cu',
                                        'adminStatus' => $adminStatus,
                                        'operStatus' => $operStatus,
                                        'vlan' => $vlan,
                                        'speed' => $speed,
                                        'port' => $port,
                                    ];
                                }
                            } else {
                                $rows[$r][] = null;
                            }
                        }
                    }

                    $gridBlocks[] = [
                        'id' => $blockId,
                        'rows' => $rows,
                    ];
                }

                if (! empty($gridBlocks)) {
                    $gridModules[] = [
                        'id' => $moduleId,
                        'blocks' => $gridBlocks,
                    ];
                }
            }

            if (! empty($gridModules)) {
                $gridSwitches[] = [
                    'id' => $switchId,
                    'serial' => $serial,
                    'modules' => $gridModules,
                ];
            }
        }

        return [
            'switches' => $gridSwitches,
            'hasMultipleSwitches' => count($gridSwitches) > 1,
        ];
    }
}
