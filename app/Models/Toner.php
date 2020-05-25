<?php

namespace App\Models;

class Toner extends DeviceRelatedModel
{
    protected $table = 'toner';
    protected $primaryKey = 'toner_id';
    public $timestamps = false;
}
