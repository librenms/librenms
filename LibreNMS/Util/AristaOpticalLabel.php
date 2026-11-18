<?php

namespace LibreNMS\Util;

/** Resolve a PLR4 optical lane label without changing the sensor's identity. */
class AristaOpticalLabel
{
    public static function rewrite(string $description, int $sensorIndex, array $entities, array $ports): string
    {
        if (! preg_match('/^DOM (RX|TX) Power\s+For (Ethernet[0-9]+)\/[0-9]+(?: \(optical lane [1-4]\))?$/i', $description, $match)) {
            return $description;
        }
        $visited = []; $lane = null; $module = null; $parent = null;
        for ($index = $sensorIndex; $index > 0;) {
            if (isset($visited[$index]) || ! isset($entities[$index])) {
                return $description;
            }
            $visited[$index] = true;
            $entity = $entities[$index];
            if (($entity['entPhysicalClass'] ?? null) === 'module'
                && preg_match('/^Lane [0-9]+ for Xcvr for Ethernet[0-9]+$/', $entity['entPhysicalName'] ?? '')) {
                if ($lane !== null || ! isset($entity['entPhysicalParentRelPos']) || ! is_numeric($entity['entPhysicalParentRelPos'])) {
                    return $description;
                }
                $lane = (int) $entity['entPhysicalParentRelPos'];
            }
            if (($entity['entPhysicalClass'] ?? null) === 'container'
                && preg_match('/^Xcvr for (Ethernet[0-9]+):/', $entity['entPhysicalName'] ?? '', $moduleMatch)) {
                if (! str_contains($entity['entPhysicalDescr'] ?? '', '400GBASE-PLR4')) {
                    return $description;
                }
                if ($moduleMatch[1] !== $match[2]) {
                    return $description;
                }
                $module = $index; $parent = $moduleMatch[1];
                break;
            }
            $index = (int) ($entity['entPhysicalContainedIn'] ?? 0);
        }
        if ($module === null || $lane === null || $lane < 0 || $lane > 3) {
            return $description;
        }
        $positions = [];
        foreach ($entities as $entity) {
            if ((int) ($entity['entPhysicalContainedIn'] ?? 0) === $module
                && ($entity['entPhysicalClass'] ?? null) === 'module'
                && preg_match('/^Lane [0-9]+ for Xcvr for ' . preg_quote($parent, '/') . '$/', $entity['entPhysicalName'] ?? '')) {
                $position = $entity['entPhysicalParentRelPos'] ?? null;
                if (! is_numeric($position) || isset($positions[(int) $position])) {
                    return $description;
                }
                $positions[(int) $position] = true;
            }
        }
        foreach ([0, 1, 2, 3] as $required) {
            if (! isset($positions[$required])) {
                return $description;
            }
        }
        $interfaces = []; $indexes = [];
        foreach ($ports as $port) {
            $name = $port['ifName'] ?? '';
            if ($name === $parent) {
                return $description; // Parent and breakout interfaces present: ambiguous.
            }
            if (! str_starts_with($name, $parent . '/')) {
                continue;
            }
            if (! preg_match('/^' . preg_quote($parent, '/') . '\/([0-9]+)$/', $name, $suffix)
                || ! isset($port['ifIndex']) || isset($indexes[$port['ifIndex']])
                || isset($interfaces[(int) $suffix[1]]) || (int) ($port['ifSpeed'] ?? 0) !== 100000000000) {
                return $description;
            }
            $indexes[$port['ifIndex']] = true;
            $interfaces[(int) $suffix[1]] = $name;
        }
        ksort($interfaces);
        if (array_keys($interfaces) !== [1, 3, 5, 7] && array_keys($interfaces) !== [1, 2, 3, 4]) {
            return $description;
        }
        $logical = array_values($interfaces)[$lane];

        return 'DOM ' . strtoupper($match[1]) . ' Power For ' . $logical . ' (optical lane ' . ($lane + 1) . ')';
    }
}
