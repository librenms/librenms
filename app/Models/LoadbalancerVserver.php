<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoadbalancerVserver extends Model
{
    protected $table = 'loadbalancer_vservers';
    protected $primaryKey = 'vserver_id';
    public $timestamps = false;
}
