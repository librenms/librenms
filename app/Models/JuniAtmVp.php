<?php

namespace App\Models;

class JuniAtmVp extends PortRelatedModel
{
    protected $table = 'juniAtmVp';
    public $timestamps = false;

    protected $fillable = ['port_id', 'vp_id', 'vp_descr'];
}
