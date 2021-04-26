<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LibreNMS\Interfaces\Models\Keyable;

class MplsLspPath extends Model implements Keyable
{
    protected $primaryKey = 'lsp_path_id';
    public $timestamps = false;
    protected $fillable = [
        'lsp_id',
        'path_oid',
        'device_id',
        'mplsLspPathRowStatus',
        'mplsLspPathLastChange',
        'mplsLspPathType',
        'mplsLspPathBandwidth',
        'mplsLspPathOperBandwidth',
        'mplsLspPathAdminState',
        'mplsLspPathOperState',
        'mplsLspPathState',
        'mplsLspPathFailCode',
        'mplsLspPathFailNodeAddr',
        'mplsLspPathMetric',
        'mplsLspPathOperMetric',
        'mplsLspPathTimeUp',
        'mplsLspPathTimeDown',
        'mplsLspPathTransitionCount',
        'mplsLspPathTunnelARHopListIndex',
        'mplsLspPathTunnelCHopListIndex',
    ];

    // ---- Helper Functions ----

    /**
     * Get a string that can identify a unique instance of this model
     * @return string
     */
    public function getCompositeKey()
    {
        return $this->lsp_id . '-' . $this->path_oid;
    }

    // ---- Define Relationships ----

    public function lsp(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MplsLsp::class, 'lsp_id');
    }
}
