# Redis High-Availability with Sentinel

## Overview

High availability with Redis needs several Redis nodes and several
Sentinel nodes. A typical production setup has 3 Redis instances and 3
Redis Sentinel processes on separate servers. A quorum after a failure
needs three nodes.

For more information about high availability with Redis and Sentinel,
read the official [Redis Sentinel
Documentation](https://redis.io/docs/latest/operate/oss_and_stack/management/sentinel/).

## Configure Redis Sentinel cluster with LibreNMS

The LibreNMS web interface and the poller can both use a Redis Sentinel
cluster as their backend.

### Web UI Configuration

For a Redis Sentinel cluster in the web interface, add these parameters
to your `.env` file:

```
# Configure these values according to your environment
REDIS_SENTINEL_HOSTS=redis://192.168.1.10:26379,redis://192.168.1.11:26379,redis://192.168.1.12:26379
REDIS_SENTINEL_SERVICE=mymaster
# optionally set password if your redis-backend has it enabled, this is not for sentinel.
REDIS_PASSWORD=your_redis_password

# These values tell the web app to use Sentinel as the Redis backend - do not change
REDIS_BROADCAST_CONNECTION=sentinel_cache
REDIS_CACHE_CONNECTION=sentinel_cache
REDIS_LOCK_CACHE_CONNECTION=sentinel_cache
SESSION_DRIVER=redis
SESSION_CONNECTION=sentinel_session
```

### Redis Sentinel Authentication

If your Redis Sentinel cluster has a password, add
`password=your_redis_password` to the end of each Redis Sentinel URL in
the `REDIS_SENTINEL_HOSTS` variable.
With ACLs, also add `username=your_redis_username` to the URL.

For example:

```
REDIS_SENTINEL_HOSTS=redis://192.168.1.10:26379?password=your_redis_password,redis://192.168.1.11:26379?password=your_redis_password,redis://192.168.1.12:26379?password=your_redis_password
```

### Poller Configuration

For a Redis Sentinel cluster in the poller, add these lines to your
`.env` file:

```
# Configure these values according to your environment
REDIS_SENTINEL=redis-001.example.org:26379,redis-002.example.org:26379,redis-003.example.org:26379
REDIS_SENTINEL_SERVICE=mymaster

# If requirepass is set in sentinel (recommended)
REDIS_SENTINEL_PASSWORD=SentinelPasswordGoesHere

# If ACLs are in use (highly recommended)
REDIS_SENTINEL_USERNAME=SentinelUsernameGoesHere
REDIS_PASSWORD=your_redis_password
```

For more information about the distributed polling configuration, read
[Distributed-Poller.md](Distributed-Poller.md#redis).

## Testing Your Configuration

To test your Redis Sentinel setup with LibreNMS, connect to the Redis
Sentinel cluster with `redis-cli`. Then run the `MONITOR` command. This
command shows the commands of the Redis cluster.
