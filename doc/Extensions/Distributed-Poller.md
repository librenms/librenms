# Distributed Polling

**Distributed Polling** enables LibreNMS to spread polling and discovery tasks across multiple servers for horizontal scaling.

A single poller can typically handle up to **1,000+ devices**, depending on factors like latency and device responsiveness.
Before deploying distributed polling, review the [Performance Documentation](../Support/Performance.md) to ensure your system is fully optimized.

> **Note:** Distributed polling is **not intended for remote polling**.

---

## Overview

In addition to separating LibreNMS components across different servers, distributed polling allows poller workloads to be balanced among multiple nodes.

LibreNMS consists of several core services:

- Poller, Discovery, and related workers
- RRD (time-series data store)
- Database
- Web Server (UI/API)

Distributed Polling also requires:

- [The Dispatcher Service](Dispatcher-Service.md)
- [RRDCached](RRDCached.md)
- [Redis](#redis)

All poller nodes must connect to the same instance of:

- Database
- RRDCached
- Redis

---

## Redis

Distributed Polling uses **Redis** to coordinate polling nodes.

Install and configure Redis on a shared server, then set the following environment variables in the `.env` file on **all nodes**:

```dotenv
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_DB=0
REDIS_TIMEOUT=60

# If Redis authentication is enabled (recommended):
REDIS_PASSWORD=PasswordGoesHere

# If Redis ACLs are in use (recommended):
REDIS_USERNAME=UsernameGoesHere
```

### Sentinel

If you use Redis Sentinel, you may still need to define
`REDIS_PASSWORD`, `REDIS_USERNAME`, `REDIS_DB`, and `REDIS_TIMEOUT`.

Sentinel provides high availability and automatic failover.
Authentication can (and should) be enabled for both Sentinel and Redis instances.

```dotenv
REDIS_SENTINEL=redis-001.example.org:26379,redis-002.example.org:26379,redis-003.example.org:26379
REDIS_SENTINEL_SERVICE=mymaster

# If Sentinel authentication is enabled (recommended):
REDIS_SENTINEL_PASSWORD=SentinelPasswordGoesHere
REDIS_SENTINEL_USERNAME=SentinelUsernameGoesHere
```

### Redis Security

See <https://redis.io/docs/management/security/acl/> for details on Redis ACLs and security best practices.

### Caching, Locks, and Sessions

Since Redis is already configured, enable it for caching, queues, and sessions:

```dotenv
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

---

## Configuration

Each node requires valid connection settings in `.env`.
This file is generated after running Composer and setting both `APP_KEY` and `NODE_ID`.

!!! warning
    `APP_KEY` must be **identical** across all nodes.

    `NODE_ID` must be **unique** per node.

```dotenv
APP_KEY=   # Required - same on all nodes
NODE_ID=   # Required - unique per node

DB_HOST=localhost
DB_DATABASE=librenms
DB_USERNAME=librenms
DB_PASSWORD=
```

---

## Poller Groups

Poller groups allow you to assign devices to specific pollers or sets of pollers.
By default, all devices and pollers belong to **group 0**.

Enable distributed polling to expose poller group options in the Web UI:

!!! setting "poller/distributed"
    ```bash
    lnms config:set distributed_poller true
    ```

### Creating Poller Groups

In the Web UI, go to **Settings > Poller > Groups** to create groups.

### Assigning Poller Nodes to Groups

In **Settings > Poller > Settings**, choose poller group(s) for each node.

You can also set poller groups manually in `config.php` (though this is overridden by per-node Web UI settings):

```php
$config['distributed_poller_group'] = '1,2,3';
```

### Assigning Devices to a Poller Group

You can assign devices to a poller group when adding or editing them.
To change the default poller group:

!!! setting "poller/distributed"
    ```bash
    lnms config:set default_poller_group 1
    ```

### Distributed Billing

By default, billing runs on a single poller.
To allow billing across groups:

!!! setting "poller/distributed"
    ```bash
    lnms config:set distributed_billing true
    ```

---

## Scaling

Scale gradually to simplify management and maintain reliability.
Stop when you are able to handle your work load.

1. Start with a stable single-server installation.
2. Enable [RRDCached](RRDCached.md).
3. Switch to [The Dispatcher Service](Dispatcher-Service.md).
4. Review [Performance Documentation](../Support/Performance.md).
5. Move services to separate servers as needed:
    - Database
    - RRDCached
    - Web Server (UI/API)
    - Poller
6. Configure Redis.
7. Add an additional poller node.
8. Add more pollers as required.
9. Use poller groups to control how devices are distributed across nodes.

---

## High Availability

Electrocret said he is going to write a guide on this. :D

---

## Dispatcher-Only Node

For nodes dedicated solely to polling, you can skip certain setup steps to streamline installation.

Do **not** install or configure:

- Database
- Web Server
- Web Installer
- Cron Scripts

Follow the [installation guide](../Installation/Install-LibreNMS.md), skipping database and web configuration steps.
When prompted for the web install, instead copy the `.env` file from another node and assign a unique `NODE_ID`.

Then set up the [Dispatcher Service](Dispatcher-Service.md).
The poller node will appear in the Web UI once it starts reporting.
