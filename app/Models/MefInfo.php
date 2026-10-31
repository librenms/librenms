<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class MefInfo extends DeviceRelatedModel
{
    use HasFactory;

    protected $table = 'mefinfo';
    public $timestamps = false;

    protected $fillable = ['device_id', 'mefID', 'mefType', 'mefIdent', 'mefMTU', 'mefAdmState', 'mefRowState'];
}
