# High Availability

## Overview

High Availability (HA) in LibreNMS ensures continuous operation and minimizes downtime by implementing redundancy for two critical components:

- **Polling**: The data collection process
- **WebUI**: The web interface for users

To achieve high availability, you need to ensure that the following components are redundant:

1. **Database**: MySQL/MariaDB with clustering
2. **Redis w/ Redis Sentinel**: For session management and caching
3. **RRD Files**: For storing polled data

Also make sure that the **poller uses a distributed setup** which must be [LibreNMS Dispatcher service](../Extensions/Dispatcher-Service.md).

For simplicity, the web-ui and poller can be configured to use the same Redis Sentinel cluster.

## Note about RRD Files

Pollers need to write RRD data to files on disk to store polled data. To ensure high availability, it's recommended to 
use RRDCached which accept RRD data over TCP/IP. 
This allows multiple pollers to write to the same RRD files using network connection.
This is outlined in [RRDCached.md](../Extensions/RRDCached.md).

You can also use a shared storage for the RRD files over NFS with GlusterFS or similar.


## WebUI High Availability

The WebUI achieves HA through multiple LibreNMS instances sharing these backend services:

- Clustered Database
- Redis with Sentinel
- Centralized RRD Storage

### Implementation

1. **Configure Database HA**: 
   - Set up a Galera Cluster
   - See [Galera-Cluster.md](../Extensions/Galera-Cluster.md) for detailed instructions

2. **Configure Redis HA**:
   - Implement Redis Sentinel
   - See [Redis-Sentinel.md](../Extensions/Redis-Sentinel.md) for configuration details

3. **Deploy multiple LibreNMS instances**:
   - Install LibreNMS on multiple servers
   - Configure each instance to use the same database and Redis Sentinel cluster
   - Ensure identical `.env` configurations across all instances. Remember to set `APP_KEY` to the same value on all instances.

4. **Configure RRD Access**:
    Either use Use RRDCached that allows all instances to access the same RRD files. Or use a shared storage for the RRD files over NFS or similar.

## Polling High Availability

Distributed polling allows multiple pollers to work together, providing load distribution and failover capability.

Important! The poller does not support MySQL Galera clustering, so you need to use a TCP load balancer such as Nginx or HAProxy
in front of the cluster to point to the cluster nodes.

### Implementation

1. **Configure distributed polling**:
   - Follow the instructions in [Distributed-Poller.md](../Extensions/Distributed-Poller.md)
   - Ensure all pollers connect to the clustered database, Redis Sentinel and can access the same RRD files.
