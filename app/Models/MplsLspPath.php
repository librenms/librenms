<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LibreNMS\Interfaces\Models\Keyable;

class MplsLspPath extends DeviceRelatedModel implements Keyable
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
     */
    public function getCompositeKey(): string
    {
        return $this->lsp_id . '-' . $this->path_oid;
    }

    // ---- Define Relationships ----
    /**
     * @return BelongsTo<MplsLsp, $this>
     */
    public function lsp(): BelongsTo
    {
        return $this->belongsTo(MplsLsp::class, 'lsp_id');
    }

    /**
     * @return HasMany<MplsTunnelArHop, $this>
     */
    public function arHops(): HasMany
    {
        return $this->hasMany(MplsTunnelArHop::class, 'lsp_path_id');
    }

    /**
     * @return HasMany<MplsTunnelCHop, $this>
     */
    public function cHops(): HasMany
    {
        return $this->hasMany(MplsTunnelCHop::class, 'lsp_path_id');
    }
}
