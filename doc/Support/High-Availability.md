# High Availability

## Overview

High availability (HA) in LibreNMS keeps the operation continuous and
makes the downtime as short as possible. HA gives redundancy to two
critical components:

- **Polling**: the data collection process
- **WebUI**: the web interface for the users

For high availability, these components must be redundant:

1. **Database**: MySQL or MariaDB with clustering
2. **Redis with Redis Sentinel**: for session management and caching
3. **RRD Files**: for the storage of the polled data

The **poller must use a distributed setup**. This setup must be the
[LibreNMS Dispatcher service](../Extensions/Dispatcher-Service.md).

For a simpler design, the web interface and the poller can use the same
Redis Sentinel cluster.

## Note about RRD Files

A poller writes RRD data to files on a disk. We recommend RRDCached,
which accepts RRD data over TCP/IP. RRDCached does not give HA for the
RRD data. It lets many pollers write to the same RRD files over a
network connection. For more information, read
[RRDCached.md](../Extensions/RRDCached.md).

Shared storage over NFS with GlusterFS is one way to add HA for RRD.

## WebUI High Availability

For HA, many LibreNMS instances share these backend services:

- Clustered Database
- Redis with Sentinel
- Centralized RRD Storage

### Implementation

1. **Configure Database HA**: 
   - Build a Galera Cluster
   - For the full instructions, read [Galera-Cluster.md](../Extensions/Galera-Cluster.md)

2. **Configure Redis HA**:
   - Install Redis Sentinel
   - For the configuration details, read [Redis-Sentinel.md](../Extensions/Redis-Sentinel.md)

3. **Deploy multiple LibreNMS instances**:
   - Install LibreNMS on more than one server
   - Configure each instance for the same database and the same Redis Sentinel cluster
   - Make the `.env` configuration identical on all instances. Set `APP_KEY` to the same value on all instances.
   - Give each install a unique `NODE_ID` in the `.env` file.

4. **Configure RRD Access**:
    Use RRDCached, which gives all instances access to the same RRD files. You can also use shared storage for the RRD files over NFS.

## Polling High Availability

With distributed polling, many pollers work together. This design
distributes the load and gives failover capability.

!!! warning
    The poller does not support MySQL Galera clustering. Put a TCP load
    balancer, such as Nginx or HAProxy, in front of the cluster. The
    load balancer then points to the cluster nodes.

### Implementation

1. **Configure distributed polling**:
   - Obey the instructions in [Distributed-Poller.md](../Extensions/Distributed-Poller.md)
   - Connect all pollers to the clustered database and to Redis Sentinel. All pollers must reach the same RRD files.
