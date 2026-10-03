<?php

namespace App\Models;

use App\Observers\ProcessorObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use LibreNMS\Interfaces\Models\Keyable;
use LibreNMS\Util\Number;

/**
 * @property int $hrDeviceIndex
 * @property string|null $processor_oid
 * @property string $processor_type
 * @property float|int|string|null $processor_usage
 * @property string $processor_descr
 * @property int|null $processor_perc_warn
 * @property int|null $processor_perc_warn_custom
 */
#[ObservedBy([ProcessorObserver::class])]
class Processor extends DeviceRelatedModel implements Keyable
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'processor_id';
    protected $attributes = [
        'hrDeviceIndex' => 0,
        'processor_descr' => 'Processor',
        'processor_precision' => 1,
    ];
    protected $fillable = [
        'hrDeviceIndex',
        'processor_oid',
        'processor_index',
        'processor_type',
        'processor_usage',
        'processor_descr',
        'processor_precision',
        'processor_perc_warn',
    ];

    /**
     * Fill processor_precision first so processor_usage is scaled correctly regardless of attribute order.
     */
    public function fill(array $attributes): static
    {
        if (array_key_exists('processor_precision', $attributes)) {
            $attributes = ['processor_precision' => $attributes['processor_precision']] + $attributes;
        }

        return parent::fill($attributes);
    }

    // ---- Attribute Mutators / Casting ----

    protected function hrDeviceIndex(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => (int) $value,
        );
    }

    protected function processorDescr(): Attribute
    {
        return Attribute::make(
            set: function (?string $value) {
                $descr = trim(preg_replace('/ {2,}/', ' ', (string) $value));

                return $descr === '' ? 'Processor' : substr($descr, 0, 64);
            },
        );
    }

    protected function processorOid(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value === null ? null : '.' . ltrim($value, '.'),
        );
    }

    protected function processorType(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => substr((string) $value, 0, 16),
        );
    }

    protected function processorUsage(): Attribute
    {
        return Attribute::make(
            set: function (?string $value, array $attributes) {
                if ($value === null) {
                    return null;
                }

                // negative precision represents free, subtract from 100
                $precision = ($attributes['processor_precision'] ?? 1) ?: 1;
                $base = $precision < 0 ? 100 : 0;
                $raw_usage = Number::extract($value);

                return $base + ($raw_usage / $precision);
            }
        );
    }

    public function getCompositeKey(): string
    {
        return $this->processor_type . '_' . $this->processor_index;
    }
}
