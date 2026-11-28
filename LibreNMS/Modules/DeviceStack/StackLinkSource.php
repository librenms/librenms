<?php

namespace LibreNMS\Modules\DeviceStack;

use Illuminate\Support\Collection;
use LibreNMS\OS;

/**
 * Vendor-specific source of normalized stack links for the generic
 * DeviceStack module.
 */
interface StackLinkSource
{
    /**
     * The device's stack links, keyed by ifIndexOffset.
     *
     * @return Collection<int, StackLink>
     */
    public function stackLinks(OS $os): Collection;
}
