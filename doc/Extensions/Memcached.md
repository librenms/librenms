# Memcached

LibreNMS can store SQL results in memcached to achieve performance advantages of in-memory value storage and removing work load for frequent queries off the MySQL backend.

To enable memcached in your install you need to have `memcached` installed and the PHP extension `php5-memcached` or `php-memcached` and add the following lines to your `config.php`:

```php
$config['memcached']['enable']  = true;
$config['memcached']['host']    = "localhost";
$config['memcached']['port']    = 11211;
```

By default values are kept for 4 Minutes inside the memcached, you can adjust this retention time by modifying the `$config['memcached']['ttl']` value to any desired amount of seconds.
It's strongly discouraged to set this above `300` (5 Minutes) to avoid interferences with the polling, discovery and alerting processes.

If you use the Distributed Poller, you can point this to the same memcached instance. However a local memcached will perform better in any case.

By default `memcached` on many distributions starts itself with 64 MB of memory for it to store data in. If you have lots of devices or look at graphs frequently, it might be worth it to expand `memcached`'s footprint a bit. Generally this can be done in `/etc/memcached.conf`, replacing `-m 64` with `-m 512`, or however many megs of memory you want to allocate for `memcached`. Then restart the `memcached` service.
