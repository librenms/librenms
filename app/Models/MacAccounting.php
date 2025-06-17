<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'packets_in',
        'packets_out',
        'last_polled',
    ];

    protected function bytesIn(): Attribute
    {
        return Attribute::set(function ($value) {
            if (empty($this->last_polled)) {
                $this->attributes['bps_in'] = 0;

                return $value;
            }

            $this->attributes['bps_in'] = Number::calculateRate(
                (string) ($this->getAttribute('bytes_in') ?: '0'),
                (string) ($value ?: '0'),
                $this->last_polled,
                time()
            ) * 8;

            return $value;
        });
    }

    protected function bytesOut(): Attribute
    {
        return Attribute::set(function ($value) {
            if (empty($this->last_polled)) {
                $this->attributes['bps_out'] = 0;

                return $value;
            }

            $this->attributes['bps_out'] = Number::calculateRate(
                (string) ($this->getAttribute('bytes_in') ?: '0'),
                (string) ($value ?: '0'),
                $this->last_polled,
                time()
            ) * 8;

            return $value;
        });
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
