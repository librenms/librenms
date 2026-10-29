<?php

/**
 * InstallationChecksController.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Install;

use LibreNMS\Interfaces\InstallerStep;
use LibreNMS\Util\Version;
use LibreNMS\Validations\Php;
use LibreNMS\Validations\Programs;
use LibreNMS\Validations\Snmp;

class ChecksController extends InstallationController implements InstallerStep
{
    protected $step = 'checks';

    /** @var array<int, array{name: string, version: string, required: string, ok: bool}>|null */
    private ?array $versions = null;

    public function index()
    {
        $this->initInstallStep();

        if ($this->complete()) {
            $this->markStepComplete();
        }

        return view('install.checks', $this->formatData([
            'versions' => $this->versionResults(),
        ]));
    }

    /**
     * Installed versions of PHP and the required programs, which run the version checks once
     *
     * @return array<int, array{name: string, version: string, required: string, ok: bool}>
     */
    private function versionResults(): array
    {
        if ($this->versions === null) {
            preg_match('/\d+\.\d+\.\d+/', PHP_VERSION, $matches);
            $version = Version::get();

            $this->versions = array_map(fn (array $item): array => $item + [
                'ok' => $item['version'] !== '' && version_compare($item['version'], $item['required'], '>='),
            ], [
                ['name' => 'PHP', 'version' => $matches[0] ?? PHP_VERSION, 'required' => Php::PHP_MIN_VERSION],
                ['name' => 'net-snmp', 'version' => $version->netSnmp(), 'required' => Snmp::NET_SNMP_MIN_VERSION],
                ['name' => 'fping', 'version' => $version->fping(), 'required' => Programs::FPING_MIN_VERSION],
            ]);
        }

        return $this->versions;
    }

    public function complete(): bool
    {
        if ($this->stepCompleted('checks')) {
            return true;
        }

        foreach ($this->versionResults() as $result) {
            if (! $result['ok']) {
                return false;
            }
        }

        return true;
    }

    public function enabled(): bool
    {
        return true;
    }

    public function icon(): string
    {
        return 'fa-solid fa-list-ul fa-flip-horizontal';
    }
}
