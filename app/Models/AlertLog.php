<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AlertLog extends DeviceRelatedModel
{
    use HasFactory;

    public const UPDATED_AT = null;
    public const CREATED_AT = 'time_logged';
    protected $table = 'alert_log';

    protected function details(): Attribute
    {
        return Attribute::make(
            get: fn ($details) => json_decode(@gzuncompress($details), true) ?? [],
            set: fn ($details) => gzcompress(json_encode($details)),
        )->shouldCache();
    }
}
