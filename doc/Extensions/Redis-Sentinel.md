# Redis High-Availability with Sentinel

## Overview

High-Availability with Redis can be achieved through multiple Redis nodes connected with multiple Sentinel nodes. A typical production setup includes 3 Redis instances with 3 Redis Sentinel processes running on separate servers. Three nodes are required to establish a quorum in case of a failure.

For more information about high availability using Redis with Sentinel, refer to the official [Redis Sentinel Documentation](https://redis.io/docs/latest/operate/oss_and_stack/management/sentinel/).

## Configure Redis Sentinel cluster with LibreNMS

Both LibreNMS WebUI and Poller can be configured to use a Redis Sentinel cluster as their backend.

### Web UI Configuration

To configure the Web UI to use a Redis Sentinel cluster, add the following parameters to your `.env` file:

```
# Configure these values according to your environment
REDIS_SENTINEL=192.168.1.10:26379,192.168.1.11:26379,192.168.1.12:26379
REDIS_SENTINEL_SERVICE=mymaster
REDIS_SENTINEL_PASSWORD=your_sentinel_password
REDIS_PASSWORD=your_redis_password

# These values tell the web app to use Sentinel as the Redis backend - do not change
REDIS_BROADCAST_CONNECTION=sentinel_cache
REDIS_CACHE_CONNECTION=sentinel_cache
REDIS_LOCK_CACHE_CONNECTION=sentinel_cache
SESSION_CONNECTION=sentinel_session
```

### Poller Configuration

To configure the Poller to use a Redis Sentinel cluster, add the following to your `.env` file:

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

For additional information on distributed polling configuration, see [Distributed-Poller.md](Distributed-Poller.md#using-redis).

## Testing Your Configuration

To verify your Redis Sentinel setup is working correctly with LibreNMS you can connect to the Redis Sentinel cluster using the `redis-cli` and execute the `MONITOR` command. This command will show you the commands being executed on the Redis cluster.
