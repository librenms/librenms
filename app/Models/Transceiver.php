<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use LibreNMS\Interfaces\Models\Keyable;

class Transceiver extends PortRelatedModel implements Keyable
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'port_id',
        'index', // sfp index to identify this sfp uniquely by snmp data
        'type', // module type sfp, xfp, sfp+, qsfp, sfp28, etc or more detailed info like 10G_BASE_SR_SFP
        'entity_physical_index', // maps to inventory item, should be unique per port
        'vendor', // vendor name
        'oui', // vendor oui
        'model', // model number or name
        'revision', // hardware revision
        'serial', // serial number
        'date', // date of manufacture
        'ddm', // if the module supports DDM or DOM
        'encoding', // data encoding method
        'cable', // SM, MM, Copper, etc
        'distance', // Max distance or measured distance
        'wavelength', // Middle Wavelength in nanometers
        'connector', // LC, SM, RJ45, etc physical connector type, See Ocnos for some normalized connectors
        'channels', // number of channels or lanes
    ];

    protected $casts = ['ddm' => 'boolean'];

    public function getCompositeKey()
    {
        return $this->index;
    }
}
