<?php

namespace App\Models;

use App\Data\TimeSeries\MetricIdentity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JuniAtmVp extends PortRelatedModel
{
    /** @use HasFactory<\Database\Factories\JuniAtmVpFactory> */
    use HasFactory;

    protected $table = 'juniAtmVp';
    public $timestamps = false;

    protected $fillable = ['port_id', 'vp_id', 'vp_descr', 'juniAtmVp_id'];

    /**
     * Requires the port and its device to be loaded
     */
    public function metricIdentity(string $measurement): MetricIdentity
    {
        return new MetricIdentity($measurement, [
            'hostname' => (string) $this->port?->device?->hostname,
            'ifIndex' => (int) $this->port?->ifIndex,
            'vp' => $this->vp_id,
        ]);
    }
}
