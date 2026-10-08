<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeeringdbIxPeer extends Model
{
    protected $table = 'pdb_ix_peers';
    protected $primaryKey = 'pdb_ix_peers_id';
    public $timestamps = false;
    protected $fillable = ['ix_id', 'peer_id', 'remote_asn', 'remote_ipaddr4', 'remote_ipaddr6', 'name', 'timestamp'];
}
