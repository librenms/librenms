<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LibreNMS\Interfaces\Models\Keyable;
use LibreNMS\Util\Number;

class MacAccounting extends PortRelatedModel implements Keyable
{
    protected $table = 'mac_accounting';
    protected $primaryKey = 'ma_id';
    public $timestamps = false;
    protected $fillable = [
        'port_id',
        'mac',
        'ifIndex',
        'vlan',
        'bps_in',
        'bps_out',
        'bytes_in',
        'bytes_out',
        'bytes_in_rate',
        'bytes_out_rate',
        'packets_in',
        'packets_out',
        'last_polled',
    ];

    public function fillRates(): void
    {
        if (empty($this->attributes['last_polled'])) {
            $this->attributes['last_polled'] = time();

            return;
        }

        $now = time();

        $prev_bytes_in = $this->getOriginal('bytes_in');
        if ($prev_bytes_in) {
            $this->attributes['bytes_in_rate'] = Number::calculateRate(
                $prev_bytes_in,
                $this->attributes['bytes_in'],
                $this->attributes['last_polled'],
                $now,
            );
            $this->attributes['bps_in'] = $this->attributes['bytes_in_rate'] * 8;
        }

        $prev_bytes_out = $this->getOriginal('bytes_out');
        if ($prev_bytes_out) {
            $this->attributes['bytes_out_rate'] = Number::calculateRate(
                $prev_bytes_out,
                $this->attributes['bytes_out'],
                $this->attributes['last_polled'],
                $now,
            );
            $this->attributes['bps_out'] = $this->attributes['bytes_out_rate'] * 8;
        }

        $this->attributes['last_polled'] = $now;
    }

    /**
     * @inheritDoc
     */
    public function getCompositeKey(): string
    {
        return $this->getAttribute('ifIndex') . '-' . $this->getAttribute('mac');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Device, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id');
    }
}
