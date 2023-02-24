<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class AlertLog extends DeviceRelatedModel
{
    use HasFactory;

    public const UPDATED_AT = null;
    public const CREATED_AT = 'time_logged';
    protected $table = 'alert_log';
}
