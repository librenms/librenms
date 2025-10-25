# Distributed Polling

Distributed polling allows the workers to be spread across additional
servers for horizontal scaling. 

A single poller can poll up to 1000 devices or more, depending on many
variables such as latency and the amount of data being polled.
Before reaching for distributed polling review the [performance documentation](../Support/Performance.md)
to ensure that your install is running well.

Distributed polling is not intended for remote polling.

## Overview

In addition to splitting up the services required to run LibreNMS to run
on separate servers, distributed polling allows polling to be spread
across multiple servers.

LibreNMS is made up of the following services:

- Poller/Discovery/etc workers
- RRD (Time series data store)
- Database
- Webserver (Web UI/API)

Distributed Polling also requires:

- [The Dispatcher Service](Dispatcher-Service.md)
- [Redis](#redis)
- [RRDCached](RRDCached.md)

All nodes must point to the same instance of following services:

 - Database
 - Redis
 - RRDCached

---

## Redis

Distributed polling uses Redis for coordination of nodes.

Install and configure Redis on a server.  Once it is set up, you
will need to set the following environment variables in the
`.env` file on all nodes:

```dotenv
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_DB=0
REDIS_TIMEOUT=60

# If requirepass is set in redis set everything above as well as: (recommended)
REDIS_PASSWORD=PasswordGoesHere

# If ACL's are in use, set everything above as well as: (recommended)
REDIS_USERNAME=UsernameGoesHere
```


### Sentinel

Note that if you use Sentinel, you may still need `REDIS_PASSWORD`, `REDIS_USERNAME`, `REDIS_DB` and `REDIS_TIMEOUT`
Sentinel just provides the address of the instance currently accepting writes and manages failover. 
It's possible (and recommended) to have authentication both on Sentinel and the managed Redis instances.


```dotenv
REDIS_SENTINEL=redis-001.example.org:26379,redis-002.example.org:26379,redis-003.example.org:26379
REDIS_SENTINEL_SERVICE=mymaster

# If requirepass is set in sentinel, set everything above as well as: (recommended)
REDIS_SENTINEL_PASSWORD=SentinelPasswordGoesHere

# If ACL's are in use, set everything above as well as: (recommended)
REDIS_SENTINEL_USERNAME=SentinelUsernameGoesHere
```

### Redis Security
For more information on ACL's, see <https://redis.io/docs/management/security/acl/>

#### Caching, Locks, and Sessions
Since you have set up Redis you should enable it for various uses in LibreNMS.

```dotenv
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

Enable distributed polling to make options show in the web ui.
!!! setting "poller/distributed"
```bash
lnms config:set distributed_poller true
```

---

## Configuration

Connection settings are required in `.env`. The `.env` file is
generated after composer install and `APP_KEY` and `NODE_ID` are set.

!!! Warning
    `APP_KEY` must be the same for all nodes.
    `NODE_ID` must be unique for each node.`

```dotenv
APP_KEY=   #Required
NODE_ID=   #Required

DB_HOST=localhost
DB_DATABASE=librenms
DB_USERNAME=librenms
DB_PASSWORD=
```

## Poller Groups

Poller groups allow you to pin devices to a single or a group of designated pollers.
By default all devices and pollers are assigned to group 0.

To show poller groups in the Web UI, set:

!!! setting "poller/distributed"
    ```bash
    lnms config:set distributed_poller true
    ```

### Creating Poller Groups
To create a poller groups go to **Settings > Poller > Groups**.

### Assigning Poller Nodes to Poller Groups

In the web UI, go to **Settings > Poller > Settings** and select the desired group for each node.

You can also set groups in `config.php`
This is overriden by the poller specific settings in the web UI and should not be set in the global configuration.

```php
$config['distributed_poller_group'] = '1,2,3';
```

### Assigning Devices to a Poller Group

You can select a poller group when adding devices or by editing the device.

You can change the default poller group by setting:

!!! setting "poller/distributed"
    ```bash
    lnms config:set default_poller_group 1
    ```

### Distributed Billing
By default billing will only run on a single poller.
To allow billing to use polling groups, set:

!!! setting "poller/distributed"
    ```bash
    lnms config:set distributed_billing true
    ```

---

## Scaling

Scaling your install gradually is the best way to keep things as simple as possible
while still being able to meet your needs.

1. First start with a working single server install
2. Set up and enable [RRDCached](RRDCached.md)
3. Switch to [The Dispatcher Service](Dispatcher-Service.md)
4. Go through the [performance documentation](../Support/Performance.md) to ensure that your install is running well.
5. Move services to additional servers. These services can be located on any server.
    - Database
    - RRDCached
    - Web Server (UI and API)
    - Poller
6. Set up and configure Redis
7. Add an additional poller
8. Add more pollers as needed
9. Consider using poller groups to pin devices to various pollers

## High Availability

Electrocret said he is going to write a guide on this. :D

---

## Setting up a dispatcher only node

For a node that intended to only run poller (and other) work we can streamline
the install by skipping the folloing things:

 - Database
 - Web Server
 - Web install
 - Cron scripts

Run through the [install documentation](../Installation/Install-LibreNMS.md) skipping all steps that
to configure database and web server.

When you get to the web install step, instead you should manually create .env by copying it from another node.
Make sure you modify `NODE_ID` to be unique.

Set up the [dispatcher service](Dispatcher-Service.md)

The poller node should now be active and show in the web ui.
