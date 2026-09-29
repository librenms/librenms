<?php

/**
 * PortIfAlias.php
 *
 * Show where the ifAlias stored for each port comes from: the device, a user
 * override, or the automatic fallback LibreNMS applies when a device does not
 * report ifAlias.
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
 * @copyright  2026 LibreNMS
 */

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Models\Device;
use Illuminate\Database\Eloquent\Builder;
use LibreNMS\Util\ConsolePager;
use SnmpQuery;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Throwable;

class PortIfAlias extends LnmsCommand
{
    protected $name = 'port:ifAlias';

    /** The device attribute the web ui writes to mark a user override. */
    private const OVERRIDE_PREFIX = 'ifName:';

    /** The value written by the web ui, meaning "use ports.ifAlias". */
    private const OVERRIDE_LEGACY = '1';

    private ConsolePager $pager;

    private int $same = 0;

    private int $different = 0;

    private int $overrides = 0;

    private int $fills = 0;

    public function __construct()
    {
        parent::__construct();

        // descriptions default to commands.port:ifAlias.arguments.* and
        // commands.port:ifAlias.options.*, see LnmsCommand::addArgument/addOption
        $this->addArgument('device spec', InputArgument::OPTIONAL, '', 'all');

        // no shortcuts for the long only options: -h, -q, -v, -V, -n and -e
        // are taken by the Symfony console itself
        $this->addOption('diff', 'd', InputOption::VALUE_NONE);
        $this->addOption('override-only', null, InputOption::VALUE_NONE);
        $this->addOption('inactive', 'o', InputOption::VALUE_NONE);
        $this->addOption('no-snmp', null, InputOption::VALUE_NONE);
        $this->addOption('pager', null, InputOption::VALUE_REQUIRED);
        $this->addOption('lines', null, InputOption::VALUE_REQUIRED, '', 30);
    }

    public function handle(): int
    {
        $this->pager = new ConsolePager(
            $this->option('pager') !== null ? (string) $this->option('pager') : null,
            max(1, (int) $this->option('lines')),
            fn (string $text) => $this->output->write($text)
        );

        $mode = $this->pager->open();

        $devices = Device::whereDeviceSpec((string) $this->argument('device spec'))
            ->orderBy('hostname')
            ->get();

        if ($devices->isEmpty()) {
            $this->pager->write(trans('commands.port:ifAlias.errors.no_device') . PHP_EOL);
            $this->pager->close();

            return self::FAILURE;
        }

        $this->pager->write($this->header($mode) . PHP_EOL);

        foreach ($devices as $device) {
            $this->processDevice($device);

            if ($this->pager->quitRequested()) {
                break;
            }
        }

        // a summary over a partial run would be misleading
        if (! $this->pager->quitRequested()) {
            $this->pager->write(PHP_EOL . $this->summary() . PHP_EOL);
        }

        $this->pager->close();

        return self::SUCCESS;
    }

    private function summary(): string
    {
        return trans('commands.port:ifAlias.summary', [
            'different' => $this->different,
            'overrides' => $this->overrides,
            'fills' => $this->fills,
            'same' => $this->same,
        ]);
    }

    private function header(string $pagerMode): string
    {
        $lines = [
            trans('commands.port:ifAlias.sources') . ' ' . __('commands.port:ifAlias.source_legend'),
            trans('commands.port:ifAlias.pager') . ' ' . $this->pagerDescription($pagerMode),
        ];

        return implode(PHP_EOL, $lines);
    }

    private function pagerDescription(string $mode): string
    {
        return match ($mode) {
            ConsolePager::MODE_LESS => (string) ($this->option('pager') ?: trans('commands.port:ifAlias.default_pager')),
            ConsolePager::MODE_ENTER => trans('commands.port:ifAlias.pager_fallback', ['lines' => $this->option('lines')]),
            default => trans('commands.port:ifAlias.pager_off'),
        };
    }

    private function processDevice(Device $device): void
    {
        $ports = $device->ports()
            ->when(! $this->option('inactive'), fn (Builder $query) => $query->where('deleted', 0)->where('disabled', 0))
            ->orderBy('ifIndex')
            ->get();

        $this->pager->page($this->deviceHeader($device, $ports->count()) . PHP_EOL);

        if ($ports->isEmpty()) {
            $this->pager->page(trans('commands.port:ifAlias.errors.no_ports') . PHP_EOL . PHP_EOL);

            return;
        }

        $device_values = $this->option('no-snmp') ? [] : $this->fetchDeviceValues($device);
        $has_snmp = ! $this->option('no-snmp');

        $this->pager->page($this->tableHeader() . PHP_EOL);

        foreach ($ports as $port) {
            $this->processPort($device, $port, $device_values, $has_snmp);

            if ($this->pager->quitRequested()) {
                return;
            }
        }

        $this->pager->page(PHP_EOL);
    }

    private function deviceHeader(Device $device, int $portCount): string
    {
        $name = $device->displayName() === $device->hostname
            ? $device->hostname
            : $device->displayName() . ' (' . $device->hostname . ')';

        return trans('commands.port:ifAlias.device', [
            'device' => $name,
            'os' => $device->os,
            'ports' => $portCount,
        ]);
    }

    /**
     * Live values from the device, keyed by ifIndex then by bare OID name.
     *
     * ifAlias and ifDescr are walked separately so that a device not
     * implementing one of them still reports the other. hideMib() is required:
     * without it the keys come back as IF-MIB::ifAlias.1 and the lookup below
     * would never match.
     */
    private function fetchDeviceValues(Device $device): array
    {
        $values = [];

        foreach (['ifAlias', 'ifDescr'] as $oid) {
            try {
                $response = SnmpQuery::make()->hideMib()->device($device)->walk([$oid]);
            } catch (Throwable) {
                continue; // not supported, the column is reported as unavailable
            }

            if (! $response->isValid()) {
                continue;
            }

            foreach ($response->valuesByIndex() as $index => $row) {
                $values[$index] = array_merge($values[$index] ?? [], $row);
            }
        }

        return $values;
    }

    private function processPort(Device $device, $port, array $device_values, bool $has_snmp): void
    {
        $db = (string) ($port->ifAlias ?? '');
        $device_alias = (string) ($device_values[$port->ifIndex]['ifAlias'] ?? '');
        $device_descr = (string) ($device_values[$port->ifIndex]['ifDescr'] ?? '');

        $override = $device->getAttrib(self::OVERRIDE_PREFIX . $port->ifName);
        $is_override = $override !== null;
        $is_different = $has_snmp && $db !== $device_alias;

        $source = $this->determineSource($is_override, $has_snmp, $device_alias, $device_descr, $db, (string) $port->ifName);

        // count every port, then filter, so the summary always describes the
        // whole install and not only what was printed
        $is_fill = str_starts_with($source, 'fill');
        if ($is_override) {
            $this->overrides++;
        }
        if ($is_fill) {
            $this->fills++;
        }
        if ($is_different) {
            $this->different++;
        } elseif ($has_snmp) {
            $this->same++;
        }

        if (! $this->shouldPrint($is_override, $is_different)) {
            return;
        }

        $this->pager->page($this->portRow(
            $port,
            $db,
            $device_alias,
            $has_snmp,
            $source,
            $is_override,
            $override,
            $is_different,
            $is_fill
        ));
    }

    /**
     * Where the stored ifAlias comes from.
     *
     * An override always wins, so it is checked first. Otherwise the device has
     * the last word, unless it reports no ifAlias at all: in that case
     * port_fill_missing_and_trim() (includes/functions.php) has copied ifDescr,
     * or ifName when there is no ifDescr either, into the ifAlias field. That
     * is LibreNMS filling a gap, not something the user asked for, and the two
     * are reported separately so they are never confused.
     */
    private function determineSource(
        bool $is_override,
        bool $has_snmp,
        string $device_alias,
        string $device_descr,
        string $db,
        string $ifName
    ): string {
        if ($is_override) {
            return 'override';
        }

        if (! $has_snmp) {
            return 'db only';
        }

        if ($device_alias !== '') {
            return 'device';
        }

        $expected = $device_descr !== '' ? $device_descr : $ifName;

        if ($expected !== '' && $db === $expected) {
            return 'fill ' . ($device_descr !== '' ? 'ifDescr' : 'ifName');
        }

        return 'fill ?';
    }

    private function shouldPrint(bool $is_override, bool $is_different): bool
    {
        if ($this->option('override-only') && ! $is_override) {
            return false;
        }

        return ! $this->option('diff') || $is_different;
    }

    private function portRow(
        $port,
        string $db,
        string $device_alias,
        bool $has_snmp,
        string $source,
        bool $is_override,
        ?string $override,
        bool $is_different,
        bool $is_fill
    ): string {
        $status = $is_different ? 'DIFFERENT' : ($has_snmp ? 'same' : 'db-only');
        $row = sprintf(
            '%-7s %-20s %-30s %-30s %-13s %s',
            $port->ifIndex,
            $this->shorten((string) $port->ifName, 18),
            $this->shorten($db, 28),
            $has_snmp ? $this->shorten($device_alias, 28) : '(not polled)',
            $source,
            $status
        ) . PHP_EOL;

        if ($is_override && $override !== self::OVERRIDE_LEGACY) {
            $row .= '           ' . trans('commands.port:ifAlias.notes.override_value', ['value' => $override]) . PHP_EOL;
        }

        if ($is_override && $override === self::OVERRIDE_LEGACY) {
            $row .= '           ' . trans('commands.port:ifAlias.notes.override_legacy') . PHP_EOL;
        }

        if ($source === 'fill ?') {
            $row .= '           ' . trans('commands.port:ifAlias.notes.fill_unknown') . PHP_EOL;
        } elseif ($is_fill) {
            $row .= '           ' . trans('commands.port:ifAlias.notes.fill', [
                'field' => $source === 'fill ifDescr' ? 'ifDescr' : 'ifName',
            ]) . PHP_EOL;
        }

        if ($is_different && ! $is_override && $device_alias !== '') {
            $row .= '           ' . trans('commands.port:ifAlias.notes.will_overwrite') . PHP_EOL;
        }

        return $row;
    }

    private function tableHeader(): string
    {
        return sprintf(
            '%-7s %-20s %-30s %-30s %-13s %s',
            'ifIndex',
            'ifName',
            'db (ports.ifAlias)',
            'snmp (IF-MIB::ifAlias)',
            'source',
            'status'
        );
    }

    private function shorten(string $value, int $width): string
    {
        return mb_strimwidth($value, 0, $width, '..');
    }
}
