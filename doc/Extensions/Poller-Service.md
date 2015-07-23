# Poller Service
The Poller service is an alternative to polling and discovery cron jobs and provides support for distributed polling without memcache. It is multi-threaded and runs continuously discovering and polling devices with the oldest data attempting to honor the polling frequency configured in `config.php`. This service replaces all the required cron jobs except for `/opt/librenms/daily.sh` and `/opt/librenms/alerts.php`.

Configure the maximum number of threads for the service in `$config['poller_service_workers']`. Configure the minimum desired polling frequency in `$config['poller_service_poll_frequency']` and the minimum desired discovery frequency in `$config['poller_service_discover_frequency']`. The service will not poll or discover devices which have data newer than this this configured age in seconds. Configure how frequently the service will attempt to poll devices which are down in `$config['poller_service_down_retry']`.

The poller service is designed to gracefully degrade. If not all devices can be polled within the configured frequency, the service will continuously poll devices refreshing as frequently as possible using the configured number of threads.

The service logs to syslog. A loglevel of INFO will print status updates every 5 minutes. Loglevel of DEBUG will print updates on every device as it is scanned.

## Configuration
```php
// Poller-Service settings
$config['poller_service_loglevel']                       = "INFO";
$config['poller_service_workers']                        = 16;
$config['poller_service_poll_frequency']                 = 300;
$config['poller_service_discover_frequency']             = 21600;
$config['poller_service_down_retry']                     = 60;
```

## Distributed Polling
Distributed polling is possible, and uses the same configuration options as are described for traditional distributed polling, except that the memcached options are not necessary. The database must be acessable from the distributed pollers, and properly configured. Remote access to the RRD directory must also be configured as described in the Distributed Poller documentation. Memcache is not required. Concurrency is managed using mysql GET_LOCK to ensure that devices are only being polled by one device at at time. The poller service is compatible with poller groups.

## Service Installation
An upstart configuration `poller-service.conf` is provided. To install run `ln -s /opt/librenms/poller-service.conf /etc/init/poller-service.conf`. The service will start on boot and can be started manually by running `start poller-service`. The service is configured to run as the user `librenms` and will fail if that user does not exist.

An LSB init script `poller-service.init` is also provided. To install run `ln -s /opt/librenms/poller-service.init /etc/init.d/poller-service && update-rc.d poller-service defaults`.
