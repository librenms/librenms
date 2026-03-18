<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeeringdbIxPeer extends Model
{
    protected $table = 'pdb_ix_peers';
    protected $primaryKey = 'pdb_ix_peers_id';
    public $timestamps = false;
}
