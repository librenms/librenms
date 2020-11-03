<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vminfo extends DeviceRelatedModel
{
    use HasFactory;

    protected $table = 'vminfo';
    public $timestamps = false;
}
