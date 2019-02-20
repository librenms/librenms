<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlertTransport extends Model
{
    protected $primaryKey = 'transport_id';
    public $timestamps = false;
    protected $casts = ['transport_config' => 'array'];
}
