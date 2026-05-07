<?php

namespace LibreNMS\Modules\DeviceStack;

use LibreNMS\Enum\IfOperStatus;

/**
 * A normalized stack link (fabric link between members of a device stack).
 *
 * Vendors provide these via a StackLinkSource; the generic DeviceStack module
 * materializes them as synthetic ports.
 */
final readonly class StackLink
{
    public function __construct(
        /**
         * Variant-specific, stable offset into the synthetic ifIndex space
         * (the module computes ifIndex = BASE + offset).
         */
        public int $ifIndexOffset,
        public string $ifDescr,
        public string $ifName,
        public IfOperStatus $status,
        /**
         * Link speed in bit/s, null if the vendor does not report it.
         */
        public ?int $ifSpeed = null,
        /**
         * Raw receive counter scaled to octets/s, null if the vendor does not report traffic.
         */
        public ?int $inCounter = null,
        /**
         * Raw transmit counter scaled to octets/s, null if the vendor does not report traffic.
         */
        public ?int $outCounter = null,
    ) {
    }
}
