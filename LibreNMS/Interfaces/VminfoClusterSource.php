<?php

namespace LibreNMS\Interfaces;

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
     * The composite keys of all VMs of the cluster, including those on nodes that are offline.
     *
     * @return list<string>
     */
    public function clusterVmKeys(): array;

    /**
     * The composite keys of the VMs on this node.
     *
     * @return list<string>
     */
    public function localVmKeys(): array;
}
