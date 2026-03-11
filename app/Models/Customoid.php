<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customoid extends DeviceRelatedModel
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'customoid_id';
    protected $table = 'customoids';
}
