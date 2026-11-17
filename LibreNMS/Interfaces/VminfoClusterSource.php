<?php

namespace LibreNMS\Interfaces;

use App\Models\Vminfo;
use Illuminate\Support\Collection;

/**
 * A source of the VMs of one node of a cluster, the VMs can migrate between nodes.
 * A VM is identified by its cluster: it keeps its row and metrics, only the device it runs on changes.
 */
interface VminfoClusterSource extends VminfoSource
{
    /**
     * Identifies the cluster among all clusters, it owns the metrics of its VMs.
     */
    public function clusterId(): string;

    /**
     * All VMs of the cluster, including those on nodes that are offline. Unsaved, with only what identifies them.
     *
     * @return Collection<int, Vminfo>
     */
    public function clusterVms(): Collection;

    /**
     * The VMs on this node. Unsaved, with only what identifies them.
     *
     * @return Collection<int, Vminfo>
     */
    public function localVms(): Collection;
}
