<?php

namespace LibreNMS\Modules\DeviceStack;

use Illuminate\Support\Collection;
use LibreNMS\Enum\IfOperStatus;
use LibreNMS\OS;
use SnmpQuery;

/**
 * Stack links of Dell Networking OS (dnOS).
 *
 * The stack fabric links are reported by
 * DELL-NETWORKING-CHASSIS-MIB::dellNetStackPortTable (indexed by unit and
 * port) and are not IF-MIB interfaces.
 *
 * dellNetStackPortLinkSpeed is measured in Gb/s. The Rx/TxDataRate columns
 * are rate counters measured in Mb/s (1 Mb/s == 125000 octets/s), so scaling
 * the raw counter yields the standard port INOCTETS/OUTOCTETS semantics.
 */
class Dnos implements StackLinkSource
{
    private const SPEED_MULTIPLIER = 1000000000; // Gb/s -> bit/s
    private const RATE_SCALE = 125000; // Mb/s rate counter -> octets/s
    private const UNIT_STEP = 1000;

    public function stackLinks(OS $os): Collection
    {
        $stack = SnmpQuery::mibs(['DELL-NETWORKING-CHASSIS-MIB'])->hideMib()->cache()->walk('dellNetStackPortEntry')->valuesByIndex();

        return collect($stack)
            ->filter(fn (array $entry): bool => (int) ($entry['dellNetStackPortRunningMode'] ?? 0) === 1) // only stack mode: ethernet mode ports report down(2) and are real IF-MIB ports
            ->map(fn (array $entry, string $index): StackLink => $this->toLink($entry, $index))
            ->keyBy(fn (StackLink $link): int => $link->ifIndexOffset);
    }

    private function toLink(array $entry, string $index): StackLink
    {
        [$unit, $port] = array_map(intval(...), explode('.', $index));

        return new StackLink(
            ifIndexOffset: $unit * self::UNIT_STEP + $port,
            ifDescr: sprintf('Stack Port %d (Unit %d)', $port, $unit),
            ifName: sprintf('STACK-%d-%d', $unit, $port),
            status: (int) ($entry['dellNetStackPortLinkStatus'] ?? 0) === 2 ? IfOperStatus::Down : IfOperStatus::Up,
            ifSpeed: (int) ($entry['dellNetStackPortLinkSpeed'] ?? 0) * self::SPEED_MULTIPLIER,
            inCounter: (int) ($entry['dellNetStackPortRxDataRate'] ?? 0) * self::RATE_SCALE,
            outCounter: (int) ($entry['dellNetStackPortTxDataRate'] ?? 0) * self::RATE_SCALE,
        );
    }
}
