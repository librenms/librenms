<?php

namespace App\Observers;

use App\Models\Eventlog;
use App\Models\Transceiver;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\Discovery\TransceiverSensorLimits;
use LibreNMS\OS;

class TransceiverObserver
{
    private const array IDENTITY_ATTRIBUTES = ['vendor', 'model', 'type', 'serial'];

    public function created(Transceiver $transceiver): void
    {
        $port = $this->portLabel($transceiver);
        $message = 'Transceiver added' . ($port === null ? '' : " on port $port") . ' (' . $this->identity($transceiver) . ')';

        Eventlog::log($message, $transceiver->device_id, 'interface', Severity::Notice, $transceiver->port_id);
    }

    /**
     * Log identity changes and notify opted-in OS handlers when a known serial changes.
     */
    public function updated(Transceiver $transceiver): void
    {
        if (! $transceiver->isDirty(self::IDENTITY_ATTRIBUTES)) {
            return;
        }

        $resetCount = 0;
        if ($transceiver->isDirty('serial')) {
            $oldSerial = trim((string) $transceiver->getOriginal('serial'));
            $newSerial = trim((string) $transceiver->serial);
            if ($oldSerial !== '' && $newSerial !== '' && $oldSerial !== $newSerial) {
                $device = \DeviceCache::get($transceiver->device_id)->attributesToArray();
                $os = OS::make($device);
                if ($os instanceof TransceiverSensorLimits) {
                    $resetCount = $os->resetTransceiverSensorLimits($transceiver);
                }
            }
        }

        $previous = $this->identity($transceiver, true);
        $current = $this->identity($transceiver);
        if ($previous === $current) {
            return;
        }

        $port = $this->portLabel($transceiver);
        $message = 'Transceiver changed' . ($port === null ? '' : " on port $port") . ": ($previous) -> ($current)";
        if ($resetCount > 0) {
            $message .= "; automatic sensor limit reset queued for $resetCount " . ($resetCount === 1 ? 'sensor' : 'sensors');
        }

        Eventlog::log($message, $transceiver->device_id, 'interface', Severity::Warning, $transceiver->port_id);
    }

    public function deleted(Transceiver $transceiver): void
    {
        $port = $this->portLabel($transceiver);
        $message = 'Transceiver removed' . ($port === null ? '' : " from port $port") . ' (' . $this->identity($transceiver) . ')';

        Eventlog::log($message, $transceiver->device_id, 'interface', Severity::Warning, $transceiver->port_id);
    }

    private function portLabel(Transceiver $transceiver): ?string
    {
        if (! $transceiver->port_id) {
            return null;
        }

        return $transceiver->port?->getShortLabel() ?: "ID $transceiver->port_id";
    }

    private function identity(Transceiver $transceiver, bool $original = false): string
    {
        $details = [];

        foreach (self::IDENTITY_ATTRIBUTES as $attribute) {
            $value = trim((string) ($original ? $transceiver->getOriginal($attribute) : $transceiver->$attribute));
            if ($value !== '') {
                $details[] = "$attribute: $value";
            }
        }

        return implode(', ', $details) ?: 'details unavailable';
    }
}
