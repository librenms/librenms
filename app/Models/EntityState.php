<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class EntityState extends DeviceRelatedModel
{
    use HasFactory;
    protected $table = 'entityState';
    protected $primaryKey = 'entity_state_id';
    public $timestamps = false;
}
