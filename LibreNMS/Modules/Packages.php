<?php

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Models\Package;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Traits\RequiresPollingMethod;
use SnmpQuery;

/**
 * Poll installed software packages from the HOST-RESOURCES-MIB hrSWInstalledTable (RFC 1514).
 *
 * The hostres extension of the device's snmpd reports each installed package in
 * hrSWInstalledName as "name_version_arch" (dpkg layout). The table carries no
 * version column, the last modification date is available as hrSWInstalledDate.
 */
class Packages implements Module
{
    use RequiresPollingMethod;

    /**
     * Manager used for packages collected from the HOST-RESOURCES-MIB
     */
    private const string MANAGER = 'host-res';

    /**
     * Placeholder architecture when the name does not carry one
     */
    private const string ARCH = 'unknown';

    /**
     * dpkg architectures terminating a "name_version_arch" name
     *
     * @var string[]
     */
    private const array DPKG_ARCHS = [
        'all',
        'amd64',
        'i386',
        'i686',
        'arm64',
        'aarch64',
        'armhf',
        'armel',
        's390x',
        'ppc64el',
        'ppc64',
        'riscv64',
        'mips64el',
        'mipsel',
        'loong64',
        'x32',
        'm68k',
        'sh4',
        'sparc64',
        'ia64',
        'hurd-i386',
        'kfreebsd-amd64',
    ];

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
            Log::info('No hrSWInstalledTable data, keeping existing packages');

            return;
        }

        $packages = $response->mapTable(fn (array $row): ?Package => $this->makePackage($row))->filter();

        if ($packages->isEmpty()) {
            Log::info('No valid hrSWInstalledTable entries, keeping existing packages');

            return;
        }

        Package::sync($os->getDevice(), $packages);

        Log::info("hrSWInstalledTable: {$packages->count()} packages");
    }

    /**
     * @param  array<string, string>  $row
     */
    private function makePackage(array $row): ?Package
    {
        $raw = trim((string) ($row['HOST-RESOURCES-MIB::hrSWInstalledName'] ?? ''));

        if ($raw === '') {
            return null;
        }

        // snmpwalk renders names carrying non-printable characters (e.g. NUL padding) as hex
        if (preg_match('/^[0-9a-f]{2}( [0-9a-f]{2})*$/i', $raw)) {
            $raw = $this->decodeHex($raw);

            if ($raw === '') {
                return null;
            }
        }

        // the table has no version column, names without a version fall back to the last modification date
        ['name' => $name, 'version' => $version, 'build' => $build, 'arch' => $arch] = $this->parseInstalledName($raw, $this->parseDate((string) ($row['HOST-RESOURCES-MIB::hrSWInstalledDate'] ?? '')));

        if ($version === '') {
            return null;
        }

        return new Package([
            'manager' => self::MANAGER,
            'name' => mb_substr($name, 0, 128),
            'arch' => mb_substr($arch, 0, 16),
            'version' => mb_substr($version, 0, 255),
            'build' => mb_substr($build, 0, 64),
            'size' => null,
            'status' => true,
        ]);
    }

    /**
     * Split an installed software name into its parts.
     * dpkg reports "name_version_arch", rpm reports "[epoch:]name-version-release"
     * without an architecture.
     *
     * @return array{name: string, version: string, build: string, arch: string}
     */
    private function parseInstalledName(string $raw, string $date): array
    {
        if (($parsed = $this->splitDpkgName($raw)) !== null) {
            return $parsed;
        }

        if (($parsed = $this->splitRpmName($raw)) !== null) {
            return $parsed;
        }

        return ['name' => $raw, 'version' => $date, 'build' => '', 'arch' => self::ARCH];
    }

    /**
     * Split a dpkg "name_version_arch" name into its parts.
     * Neither dpkg names nor versions carry underscores, so the first underscore
     * ends the name and the last one ends the version.
     *
     * @return array{name: string, version: string, build: string, arch: string}|null
     */
    private function splitDpkgName(string $raw): ?array
    {
        $name_underscore = strpos($raw, '_');
        $arch_underscore = strrpos($raw, '_');

        if ($name_underscore === false || $arch_underscore === $name_underscore) {
            return null;
        }

        $arch = substr($raw, $arch_underscore + 1);
        if (! in_array($arch, self::DPKG_ARCHS, true)) {
            return null;
        }

        return [
            'name' => substr($raw, 0, $name_underscore),
            'version' => substr($raw, $name_underscore + 1, $arch_underscore - $name_underscore - 1),
            'build' => '',
            'arch' => $arch,
        ];
    }

    /**
     * Split an rpm "[epoch:]name-version-release" name into its parts.
     * rpm versions and releases carry no dashes, so the first dash in front of a
     * digit whose remainder holds at most one dash separates the name from the
     * version (the second dash separates the version from the release).
     *
     * @return array{name: string, version: string, build: string, arch: string}|null
     */
    private function splitRpmName(string $raw): ?array
    {
        $epoch = '';

        if (preg_match('/^(\d+):(.*)$/s', $raw, $matches) === 1) {
            $epoch = $matches[1] . ':';
            $raw = $matches[2];
        }

        $boundary = null;

        if (preg_match_all('/-(?=\d)/', $raw, $matches, PREG_OFFSET_CAPTURE) > 0) {
            foreach ($matches[0] as $match) {
                if (substr_count(substr($raw, $match[1] + 1), '-') <= 1) {
                    $boundary = $match[1];
                    break;
                }
            }
        }

        if ($boundary === null) {
            return null;
        }

        $name = substr($raw, 0, $boundary);

        if ($name === '') {
            return null;
        }

        $version = substr($raw, $boundary + 1);
        $build = '';

        $release_dash = strpos($version, '-');
        if ($release_dash !== false) {
            $build = substr($version, $release_dash + 1);
            $version = substr($version, 0, $release_dash);
        }

        return [
            'name' => $name,
            'version' => $epoch . $version,
            'build' => $build,
            'arch' => self::ARCH,
        ];
    }

    /**
     * Decode a hex-encoded value as rendered by snmpwalk for strings with
     * non-printable characters.
     */
    private function decodeHex(string $raw): string
    {
        $decoded = '';
        foreach (explode(' ', $raw) as $byte) {
            $decoded .= chr((int) hexdec($byte));
        }

        return trim(str_replace("\0", '', $decoded));
    }

    /**
     * Parse a DateAndTime value into "Y-m-d H:i:s".
     * Handles the hostres rendering "2025-6-18,7:36:25.0,+2:0" and the standard
     * net-snmp rendering "+02:00 2025-06-18:07:36:25.0".
     */
    private function parseDate(string $value): string
    {
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2}),(\d{1,2}):(\d{1,2}):(\d{1,2})\.\d+,[+-]\d{1,2}:\d{1,2}$/', $value, $matches)) {
            return $this->formatDate($matches);
        }

        if (preg_match('/^[+-]\d{1,2}:\d{2}\s+(\d{4})-(\d{1,2})-(\d{1,2}):(\d{1,2}):(\d{1,2}):(\d{1,2})\.\d+$/', $value, $matches)) {
            return $this->formatDate($matches);
        }

        return '';
    }

    /**
     * @param  array<int, string>  $matches
     */
    private function formatDate(array $matches): string
    {
        return sprintf('%04d-%02d-%02d %02d:%02d:%02d', (int) $matches[1], (int) $matches[2], (int) $matches[3], (int) $matches[4], (int) $matches[5], (int) $matches[6]);
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
     * @return array<string, mixed>|null
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
