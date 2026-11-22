<?php

namespace App\Models;

use App\Observers\MefInfoObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use LibreNMS\Interfaces\Models\HasSyncProtectedAttributes;
use LibreNMS\Interfaces\Models\Keyable;

#[ObservedBy([MefInfoObserver::class])]
class MefInfo extends DeviceRelatedModel implements Keyable, HasSyncProtectedAttributes
{
    use HasFactory;

    protected $table = 'mefinfo';
    public $timestamps = false;

    protected $fillable = ['device_id', 'mefID', 'mefType', 'mefIdent', 'mefMTU', 'mefAdmState', 'mefRowState'];

    public function getCompositeKey(): int
    {
        return (int) $this->mefID;
    }

    /**
     * @return string[]
     */
    public function getSyncProtectedAttributes(): array
    {
        return ['mefType', 'mefIdent', 'mefMTU', 'mefAdmState', 'mefRowState'];
    }
}
