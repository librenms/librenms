# Worker Queues

> Status: Release Candidate

Laravel worker queues are a new way to schedule different jobs.  It uses the
in-built scheduler to run jobs as needed.  One of the results of the configuration
below is that less PHP processes are being launched, resulting in reduced CPU usage
of the polling processes.

See <https://laravel.com/docs/10.x/scheduling> and <https://laravel.com/docs/10.x/queues>

## External Requirements

### Redis

In order to queue jobs, you'll need a Redis instance. It's recommended that you do not
share the Redis database with any other system - by default, Redis supports up
to 16 databases (numbered 0-15). You can also use Redis on a single host if you want

It's strongly recommended that you deploy a resilient cluster of redis
systems, and use redis-sentinel.

You should not rely on the password for the security of your
system. See <https://redis.io/topics/security>

## Package installation

You will need to install the `supervisor` package to keep your queue worker processs running.

## Configuration

Once you have your Redis database set up, configure it in the .env file on each node. Configure the redis cache driver for distributed locking.

There are a number of options - most of them are optional if your redis instance is standalone and unauthenticated (neither recommended).

```dotenv
##
## All configurations
##
QUEUE_CONNECTION=redis

##
## Standalone
##
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_DB=0
REDIS_TIMEOUT=60

# If requirepass is set in redis set everything above as well as: (recommended)
REDIS_PASSWORD=PasswordGoesHere

# If ACL's are in use, set everything above as well as: (highly recommended)
REDIS_USERNAME=UsernameGoesHere

##
## Sentinel
##
REDIS_SENTINEL=redis-001.example.org:26379,redis-002.example.org:26379,redis-003.example.org:26379
REDIS_SENTINEL_SERVICE=mymaster

# If requirepass is set in sentinel, set everything above as well as: (recommended)
REDIS_SENTINEL_PASSWORD=SentinelPasswordGoesHere

# If ACL's are in use, set everything above as well as: (highly recommended)
REDIS_SENTINEL_USERNAME=SentinelUsernameGoesHere
```

For more information on ACL's, see <https://redis.io/docs/management/security/acl/>

Note that if you use Sentinel, you may still need `REDIS_PASSWORD`, `REDIS_USERNAME`, `REDIS_DB` and `REDIS_TIMEOUT` - Sentinel just provides the address of the instance currently accepting writes and manages failover. It's possible (and recommended) to have authentication both on Sentinel and the managed Redis instances.

### Supervisor

Create the following config file to run process pools for each job type, taking the following into account
 - Tune numprocs to suit your environment
 - The number in the `poller-0` queue represents the device_group that is going to be polled

Restart the supervisor process once the file has been created.

/etc/supervisor/conf.d/librenms.conf
```
[program:librenms-dispatcher]
process_name=%(program_name)s_%(process_num)02d
command=/opt/librenms/artisan queue:work --sleep=0.5 --max-jobs=100
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=librenms
numprocs=10
redirect_stderr=true
stdout_logfile=/opt/librenms/logs/queueworker.log

[program:librenms-poller]
process_name=%(program_name)s_%(process_num)02d
command=/opt/librenms/artisan queue:work --queue=poller-0 --sleep=0.5 --max-jobs=100
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=librenms
numprocs=16
redirect_stderr=true
stdout_logfile=/opt/librenms/logs/queueworker.log
```

### Basic Configuration

Additional configuration settings can be set in your config.

Disable polling in python worker

!!! setting "poller/distributed"
    ```bash
    lnms config:set service_poller_enabled false
    ```

If you are running a distributed poller:

```php
$config['distributed_poller']                    = true;            # Set to true to enable distributed polling
$config['distributed_poller_name']               = php_uname('n');  # Uniquely identifies the poller instance
$config['distributed_poller_group']              = 0;               # Which group to poll
```

## Cron Scripts

You need to ensure the following cron entries are commented and uncommented:
```
#*/5 * * * *   librenms    /opt/librenms/cronic /opt/librenms/poller-wrapper.py -p 64
* * * * * librenms /opt/librenms/artisan schedule:run -q
```
