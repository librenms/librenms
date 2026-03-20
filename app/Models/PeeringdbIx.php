<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeeringdbIx extends Model
{
    protected $table = 'pdb_ix';
    protected $primaryKey = 'pdb_ix_id';
    public $timestamps = false;
}
