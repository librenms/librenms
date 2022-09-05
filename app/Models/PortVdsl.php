<?php

namespace App\Models;

use LibreNMS\Interfaces\Models\Keyable;

class PortVdsl extends PortRelatedModel implements Keyable
{
    protected $table = 'ports_vdsl';
    protected $primaryKey = 'port_id';
    public $timestamps = false;
    protected $fillable = [
        'port_id',
        'xdsl2LineStatusAttainableRateDs',
        'xdsl2LineStatusAttainableRateUs',
        'xdsl2ChStatusActDataRateXtur',
        'xdsl2ChStatusActDataRateXtuc',
        'xdsl2LineStatusActAtpDs',
        'xdsl2LineStatusActAtpUs',
    ];

    /**
     * @inheritDoc
     */
    public function getCompositeKey()
    {
        return $this->port_id;
    }
}
