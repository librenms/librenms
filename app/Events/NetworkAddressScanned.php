<?php

namespace App\Events;

use App\Actions\Device\AddFoundDeviceResult;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * An address of a network scan was probed, fired once for every address.
 */
class NetworkAddressScanned
{
    use Dispatchable;

    /**
     * @param  string  $scanId  the scan the address is part of, see ScanNetworks
     */
    public function __construct(
        public string $scanId,
        public string $ip,
        public AddFoundDeviceResult $result,
    ) {
    }
}
