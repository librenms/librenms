<?php

namespace App\Models;

use LibreNMS\Interfaces\Models\HasSyncProtectedAttributes;
use LibreNMS\Interfaces\Models\Keyable;

class BgpPeerCbgp extends DeviceRelatedModel implements Keyable, HasSyncProtectedAttributes
{
    protected $table = 'bgpPeers_cbgp';
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = [
        'bgpPeerIdentifier',
        'afi',
        'safi',
        'context_name',
        'AcceptedPrefixes',
        'DeniedPrefixes',
        'PrefixAdminLimit',
        'PrefixThreshold',
        'PrefixClearThreshold',
        'AdvertisedPrefixes',
        'SuppressedPrefixes',
        'WithdrawnPrefixes',
    ];

    /**
     * Prefix counts are updated by polling, which also records the change since the last poll
     */
    public const PREFIX_COUNTERS = [
        'AcceptedPrefixes',
        'DeniedPrefixes',
        'PrefixAdminLimit',
        'PrefixThreshold',
        'PrefixClearThreshold',
        'AdvertisedPrefixes',
        'SuppressedPrefixes',
        'WithdrawnPrefixes',
    ];

    /**
     * Counters that also store their previous value and the change since it
     */
    public const DELTA_COUNTERS = [
        'AcceptedPrefixes',
        'DeniedPrefixes',
        'AdvertisedPrefixes',
        'SuppressedPrefixes',
        'WithdrawnPrefixes',
    ];

    /**
     * Zero for every counter column, they are all required
     *
     * @return array<string, int>
     */
    public static function emptyCounters(): array
    {
        $columns = self::PREFIX_COUNTERS;
        foreach (self::DELTA_COUNTERS as $counter) {
            array_push($columns, "{$counter}_delta", "{$counter}_prev");
        }

        return array_fill_keys($columns, 0);
    }

    public function getCompositeKey(): string
    {
        return "$this->bgpPeerIdentifier|$this->context_name|$this->afi|$this->safi";
    }

    public function getSyncProtectedAttributes(): array
    {
        return array_keys(self::emptyCounters());
    }

    /**
     * The table has no primary key, so updates and deletes are scoped by its unique index
     */
    protected function setKeysForSaveQuery($query)
    {
        return $query->where('device_id', $this->getOriginal('device_id', $this->device_id))
            ->where('bgpPeerIdentifier', $this->getOriginal('bgpPeerIdentifier', $this->bgpPeerIdentifier))
            ->where('context_name', $this->getOriginal('context_name', $this->context_name))
            ->where('afi', $this->getOriginal('afi', $this->afi))
            ->where('safi', $this->getOriginal('safi', $this->safi));
    }
}
