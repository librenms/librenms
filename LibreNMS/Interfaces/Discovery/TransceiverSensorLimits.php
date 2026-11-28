<?php

namespace LibreNMS\Interfaces\Discovery;

use App\Models\Transceiver;

interface TransceiverSensorLimits
{
    /**
     * Queue automatic sensor limit resets after a change between known optic serials.
     * Return the number of sensors marked for reset; preserve manually configured limits.
     */
    public function resetTransceiverSensorLimits(Transceiver $transceiver): int;
}
