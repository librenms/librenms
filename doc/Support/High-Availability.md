# High Availability

## Overview

High Availability (HA) in LibreNMS ensures continuous operation and minimizes downtime by implementing redundancy for two critical components:

- **Polling**: The data collection process
- **WebUI**: The web interface for users

## Note about RRD Files

Pollers need to write RRD data to files on disk to store polled data. To ensure high availability, it's recommended to use RRDCached which accept RRD data over TCP/IP. This allows multiple pollers to write to the same RRD files using network connection. This is outlined in [RRDCached.md](../Extensions/RRDCached.md).

You can also use a shared storage for the RRD files over NFS with GlusterFS or similar.

## WebUI High Availability

A HA setup for the WebUI is achieved by running multiple instances of LibreNMS connected to the same backend services. This allows for load balancing and failover capabilities.

### Requirements

1. **Load Balancer**: A load balancer (such as HAProxy, NGINX, or cloud-based solutions) to distribute traffic across LibreNMS instances
2. **High Availability Database**: MySQL/MariaDB with clustering
3. **High Availability Redis**: For session management and caching

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
    Either use Use RRDCached that allows all instaces to access the same RRD files. Or use a shared storage for the RRD files over NFS or similar.

## Polling High Availability

Distributed polling allows multiple pollers to work together, providing load distribution and failover capability.

### Implementation

1. **Configure distributed polling**:
   - Follow the instructions in [Distributed-Poller.md](../Extensions/Distributed-Poller.md)
   - Ensure all pollers connect to the clustered database, Redis Sentinel and can access the same RRD files.
