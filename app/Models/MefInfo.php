<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use LibreNMS\Interfaces\Models\Keyable;

class MefInfo extends DeviceRelatedModel implements Keyable
{
    use HasFactory;

    protected $table = 'mefinfo';
    public $timestamps = false;

    protected $fillable = ['device_id', 'mefID', 'mefType', 'mefIdent', 'mefMTU', 'mefAdmState', 'mefRowState'];

    public function getCompositeKey(): int
    {
        return (int) $this->mefID;
    }
}
