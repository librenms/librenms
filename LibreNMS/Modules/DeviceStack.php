<?php

namespace LibreNMS\Modules;

use App\Facades\Rrd;
use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Port;
use Illuminate\Database\Eloquent\Collection;
use LibreNMS\Enum\IfOperStatus;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\Modules\DeviceStack\Dnos;
use LibreNMS\Modules\DeviceStack\StackLink;
use LibreNMS\Modules\DeviceStack\StackLinkSource;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Traits\RequiresPollingMethod;
use LibreNMS\Util\ProgressList;

/**
 * Models the fabric links of a device stack (stacked chassis) as real ports.
 *
 * Stack links are vendor-specific and not IF-MIB interfaces, so they are
 * invisible in the port list and a broken link would never raise a port
 * alert. This module materializes one synthetic port per stack link so the
 * standard port status alerts, eventlog entries and traffic graphs apply.
 *
 * The vendor-specific data is provided by a StackLinkSource variant,
 * selected by the device OS in sourceFor().
 */
class DeviceStack implements Module
{
    use RequiresPollingMethod;

    /**
     * Synthetic ifIndex space for stack links: ifIndex = BASE + offset.
     * The offset is variant-specific and stable. Real device ifIndexes must
     * not fall into the reserved 980000..995999 range.
     */
    private const IFINDEX_BASE = 980000;
    private const IFINDEX_MAX = 995999;

    /**
     * @return string[]
     */
    public function dependencies(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function pollingMethods(Device $device): array
    {
        return [PollingMethodType::Snmp];
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $device = $os->getDevice();
        $source = $this->sourceFor($device);
        if ($source === null) {
            return;
        }

        $links = $source->stackLinks($os);

        $progress = ProgressList::start('Stack Links');

        foreach ($links as $offset => $link) {
            $if_index = self::IFINDEX_BASE + $offset;

            $port = $device->ports()->where('ifIndex', $if_index)->first();

            if ($port === null) {
                $port = $device->ports()->create([
                    'device_id' => $device->device_id,
                    'ifIndex' => $if_index,
                    'ifDescr' => $link->ifDescr,
                    'ifName' => $link->ifName,
                    'ifAlias' => $link->ifDescr,
                    'ifType' => 'propVirtual',
                    'ifSpeed' => $link->ifSpeed,
                    'ifAdminStatus' => IfOperStatus::Up,
                    'ifOperStatus' => $link->status,
                ]);
                Eventlog::log("{$port->ifDescr} Discovered", $device->device_id, 'interface', Severity::Info, $port->port_id);
                $progress->mark('+');
            } else {
                $this->applyStatusChange($port, $link->status, $device);

                // port re-discovered after previous deletion?
                $port->deleted = false;
                $port->ifDescr = $link->ifDescr;
                $port->ifName = $link->ifName;
                $port->ifAlias = $link->ifDescr;
                $port->ifType = 'propVirtual';
                $port->ifSpeed = $link->ifSpeed;
                $port->ifAdminStatus = IfOperStatus::Up;

                if ($port->isDirty()) {
                    $port->save();
                    $progress->mark('U');
                } else {
                    $progress->mark('.');
                }
            }
        }
        $progress->end();

        // mark stack ports that are no longer reported as deleted (synthetic ifIndex range only)
        $current_ifindexes = $links->keys()->map(fn (int $offset) => self::IFINDEX_BASE + $offset)->all();
        $device->ports()
            ->whereBetween('ifIndex', [self::IFINDEX_BASE, self::IFINDEX_MAX])
            ->whereNotIn('ifIndex', $current_ifindexes)
            ->where('deleted', 0)
            ->update(['deleted' => 1]);
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $device = $os->getDevice();
        $source = $this->sourceFor($device);
        if ($source === null) {
            return;
        }

        $ports = $device->ports()
            ->whereBetween('ifIndex', [self::IFINDEX_BASE, self::IFINDEX_MAX])
            ->where('deleted', 0)
            ->get();

        if ($ports->isEmpty()) {
            return;
        }

        $links = $source->stackLinks($os);

        $progress = ProgressList::start('Stack Links');
        foreach ($ports as $port) {
            $link = $links->get((int) $port->ifIndex - self::IFINDEX_BASE);
            if (! $link instanceof StackLink) {
                $progress->mark('-');
                continue; // gone from the device, discovery marks the port as deleted
            }

            if ($port->ifOperStatus !== $link->status) {
                $old = $port->ifOperStatus;
                $port->ifOperStatus_prev = $old;
                $port->ifOperStatus = $link->status;
                $old_str = $old instanceof IfOperStatus ? $old->value : 'NULL';
                Eventlog::log("ifOperStatus: $old_str -> {$link->status->value}", $device->device_id, 'interface', Severity::Notice, $port->port_id);
            }

            if ($link->ifSpeed !== null && (int) $port->ifSpeed !== $link->ifSpeed) {
                $old = (int) $port->ifSpeed;
                $port->ifSpeed_prev = $old;
                $port->ifSpeed = $link->ifSpeed;

                // tune the RRD only when the speed goes up, like the core port polling does
                if ($link->ifSpeed > $old && $link->ifSpeed > 0) {
                    Rrd::tune('port', Rrd::name($device->hostname, Rrd::portName((int) $port->port_id, '')), $link->ifSpeed);
                }
            }

            if ($link->inCounter !== null) {
                $this->updateCounter($port, 'ifInOctets', $link->inCounter);
            }
            if ($link->outCounter !== null) {
                $this->updateCounter($port, 'ifOutOctets', $link->outCounter);
            }

            if ($port->isDirty()) {
                $port->save();
            }
            $progress->mark('.');

            // write the traffic into the standard port RRD so the port graphs work
            $datastore->put($os->getDeviceArray(), 'ports', $this->tags($port), $this->fields($port));
        }
        $progress->end();
    }

    /**
     * @inheritDoc
     */
    public function dataExists(Device $device): bool
    {
        return $device->ports()
            ->whereBetween('ifIndex', [self::IFINDEX_BASE, self::IFINDEX_MAX])
            ->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->ports()
            ->whereBetween('ifIndex', [self::IFINDEX_BASE, self::IFINDEX_MAX])
            ->delete();
    }

    /**
     * @return array{stack_ports: Collection<int, Port>}|null
     */
    public function dump(Device $device, string $type): ?array
    {
        if ($type !== 'discovery') {
            return null;
        }

        $stack_ports = $device->ports()
            ->whereBetween('ifIndex', [self::IFINDEX_BASE, self::IFINDEX_MAX])
            ->where('deleted', 0)
            ->get()
            ->sortBy('ifIndex')
            ->values();

        if ($stack_ports->isEmpty()) {
            return null;
        }

        return [
            'stack_ports' => $stack_ports->makeHidden(['port_id', 'device_id']),
        ];
    }

    /**
     * The stack link source for this device's OS, null if the OS does not
     * report stack links.
     */
    private function sourceFor(Device $device): ?StackLinkSource
    {
        return match ($device->os) {
            'dnos' => new Dnos(),
            default => null,
        };
    }

    private function applyStatusChange(Port $port, IfOperStatus $status, Device $device): void
    {
        if ($port->ifOperStatus === $status) {
            return;
        }

        $old = $port->ifOperStatus;
        $port->ifOperStatus_prev = $old;
        $port->ifOperStatus = $status;
        $old_str = $old instanceof IfOperStatus ? $old->value : 'NULL';
        Eventlog::log("ifOperStatus: $old_str -> {$status->value}", $device->device_id, 'interface', Severity::Notice, $port->port_id);
    }

    /**
     * Store the raw counter and compute _prev/_delta/_rate like the core port polling does.
     */
    private function updateCounter(Port $port, string $column, int $current): void
    {
        $previous = (int) ($port->{$column} ?? 0);

        $port->{$column} = $current;
        $port->{$column . '_prev'} = $previous;

        if ($previous > 0) {
            $period = max(time() - (int) $port->poll_time, 1);
            $diff = $current - $previous;
            if ($diff < 0) {
                $diff = 0; // counter reset, same as the core
            }
            $port->{$column . '_rate'} = $diff / $period;
            $port->{$column . '_delta'} = $diff;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function tags(Port $port): array
    {
        return [
            'ifName' => $port->ifName,
            'ifAlias' => $port->ifAlias,
            'ifIndex' => (int) $port->ifIndex,
            'port_descr_type' => $port->port_descr_type,
            'rrd_name' => Rrd::portName((int) $port->port_id, ''),
            'rrd_def' => RrdDefinition::make()
                ->addDataset('INOCTETS', 'DERIVE', 0, 12500000000)
                ->addDataset('OUTOCTETS', 'DERIVE', 0, 12500000000)
                ->addDataset('INERRORS', 'DERIVE', 0, 12500000000)
                ->addDataset('OUTERRORS', 'DERIVE', 0, 12500000000)
                ->addDataset('INUCASTPKTS', 'DERIVE', 0, 12500000000)
                ->addDataset('OUTUCASTPKTS', 'DERIVE', 0, 12500000000)
                ->addDataset('INNUCASTPKTS', 'DERIVE', 0, 12500000000)
                ->addDataset('OUTNUCASTPKTS', 'DERIVE', 0, 12500000000)
                ->addDataset('INDISCARDS', 'DERIVE', 0, 12500000000)
                ->addDataset('OUTDISCARDS', 'DERIVE', 0, 12500000000)
                ->addDataset('INUNKNOWNPROTOS', 'DERIVE', 0, 12500000000)
                ->addDataset('INBROADCASTPKTS', 'DERIVE', 0, 12500000000)
                ->addDataset('OUTBROADCASTPKTS', 'DERIVE', 0, 12500000000)
                ->addDataset('INMULTICASTPKTS', 'DERIVE', 0, 12500000000)
                ->addDataset('OUTMULTICASTPKTS', 'DERIVE', 0, 12500000000),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fields(Port $port): array
    {
        $in_rate = (float) ($port->ifInOctets_rate ?? 0);
        $out_rate = (float) ($port->ifOutOctets_rate ?? 0);

        return [
            'INOCTETS' => (int) ($port->ifInOctets ?? 0),
            'OUTOCTETS' => (int) ($port->ifOutOctets ?? 0),
            'INERRORS' => null,
            'OUTERRORS' => null,
            'INUCASTPKTS' => null,
            'OUTUCASTPKTS' => null,
            'INNUCASTPKTS' => null,
            'OUTNUCASTPKTS' => null,
            'INDISCARDS' => null,
            'OUTDISCARDS' => null,
            'INUNKNOWNPROTOS' => null,
            'INBROADCASTPKTS' => null,
            'OUTBROADCASTPKTS' => null,
            'INMULTICASTPKTS' => null,
            'OUTMULTICASTPKTS' => null,
            // non rrd stats (will be filtered)
            'ifInOctets_rate' => $in_rate,
            'ifOutOctets_rate' => $out_rate,
            'ifInBits_rate' => round($in_rate * 8),
            'ifOutBits_rate' => round($out_rate * 8),
        ];
    }
}
