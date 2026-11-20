<?php

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Models\Package;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Traits\RequiresPollingMethod;
use SnmpQuery;

/**
 * Poll installed software packages from the HOST-RESOURCES-MIB (hrSWInstalledTable).
 * The table is only exposed by the hrdaemon (hostres) extension of the device's snmpd.
 */
class Packages implements Module
{
    use RequiresPollingMethod;

    /**
     * Manager used for packages collected from the HOST-RESOURCES-MIB
     */
    private const string MANAGER = 'host-res';

    /**
     * The HOST-RESOURCES-MIB carries no architecture, Package requires one
     */
    private const string ARCH = 'unknown';

    /**
     * @return string[]
     */
    public function dependencies(): array
    {
        return [];
    }

    public function pollingMethods(Device $device): array
    {
        return [PollingMethodType::Snmp];
    }

    /**
     * @inheritDoc
     */
    public function shouldDiscover(OS $os): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        // no discovery
    }

    /**
     * The unix agent reports richer package data, it is preferred when enabled.
     */
    public function shouldPoll(OS $os): bool
    {
        return $this->hasAvailableMethod($os)
            && ! $os->getDevice()->polling()->isEnabled(PollingMethodType::UnixAgent);
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $response = SnmpQuery::walk('HOST-RESOURCES-MIB::hrSWInstalledTable');

        if (! $response->isValid(true)) {
            return; // table not exposed or empty, keep existing packages
        }

        $packages = $response->mapTable(fn (array $row): ?Package => $this->makePackage($row))->filter();

        if ($packages->isEmpty()) {
            return; // no valid packages, keep existing ones
        }

        Package::sync($os->getDevice(), $packages);
    }

    /**
     * @param  array<string, string>  $row
     */
    private function makePackage(array $row): ?Package
    {
        $name = trim((string) ($row['HOST-RESOURCES-MIB::hrSWInstalledName'] ?? ''));
        $version = $this->parseVersion((string) ($row['HOST-RESOURCES-MIB::hrSWInstalledVersion'] ?? ''));

        if ($name === '' || $version === '') {
            return null;
        }

        return new Package([
            'manager' => self::MANAGER,
            'name' => mb_substr($name, 0, 128),
            'arch' => self::ARCH,
            'version' => mb_substr($version, 0, 255),
            'build' => '',
            'size' => null,
            'status' => true,
        ]);
    }

    /**
     * hrSWInstalledVersion is of type OBJECT IDENTIFIER, the version is carried by its subidentifiers
     */
    private function parseVersion(string $value): string
    {
        $value = trim($value);

        if (str_starts_with($value, 'OID:')) {
            $value = trim(substr($value, 4));
        }

        $value = ltrim($value, '.');

        return preg_match('/^\d+(\.\d+)*$/', $value) ? $value : '';
    }

    /**
     * @inheritDoc
     */
    public function dataExists(Device $device): bool
    {
        return $device->packages()->where('manager', self::MANAGER)->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->packages()->where('manager', self::MANAGER)->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        if ($type === 'discovery') {
            return null;
        }

        return [
            'packages' => $device->packages()
                ->where('manager', self::MANAGER)
                ->orderBy('name')
                ->get()->map->makeHidden(['pkg_id', 'device_id']),
        ];
    }
}
