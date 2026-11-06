<?php

namespace LibreNMS\Polling\Providers;

use App\Models\Mempool;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

/**
 * Memory pools from one polling method.
 */
interface MempoolsProvider
{
    /**
     * The mempools of the device, unsaved. Composite keys must be unique within this provider.
     *
     * @return Collection<int, Mempool>
     */
    public function discoverMempools(): Collection;

    /**
     * Update the usage of mempools this provider discovered.
     *
     * @param  EloquentCollection<int, Mempool>  $mempools
     */
    public function pollMempools(EloquentCollection $mempools): void;
}
