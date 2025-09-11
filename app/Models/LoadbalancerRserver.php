<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoadbalancerRserver extends Model
{
    protected $table = 'loadbalancer_rservers';
    protected $primaryKey = 'rserver_id';
    public $timestamps = false;
}
