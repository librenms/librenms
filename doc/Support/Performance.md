# Performance optimisations

This document will give you some guidance on optimising your setup.

The suggestions are in a rough order of how much impact they will have.

#### RRDCached

We absolutely recommend running this, it will save on IO load. [RRDCached](http://docs.librenms.org/Extensions/RRDCached/)


#### MySQL Optimisation

It's advisable after 24 hours of running MySQL that you run (MySQL Tuner)[https://raw.githubusercontent.com/major/MySQLTuner-perl/master/mysqltuner.pl]
which will make suggestions on things you can change specific to your setup.

One recommendation we can make is that you set the following in my.cnf:

```bash
innodb_flush_log_at_trx_commit = 0
```

You can also set this to 2. This will have the possibility that you could lose upto 1 second on mysql data in the event
MySQL crashes or your server does but it provides an amazing difference in IO use.


#### Polling modules

Disable polling (and discovery) modules that you do not need. You can do this globally in `config.php` like:

Disable OSPF polling
```php
$config['poller_modules']['ospf'] = 0;
```

You can disable modules globally then re-enable the module per device or the opposite way. For a list of modules please see
[Poller modules](http://docs.librenms.org/Support/Poller%20Support/)


#### Optimise poller-wrapper

The default 16 threads that `poller-wrapper.py` runs as isn't necessarily the optimal number. A general rule of thumb is 
2 threads per core but we suggest that you play around with lowering / increasing the number until you get the optimal value.

#### Recursive DNS

If your install uses hostnames for devices and you have quite a lot then it's advisable to setup a local recursive dns instance on the 
LibreNMS server. Something like pdns-recursor can be used and then configure `/etc/resolv.conf` to use 127.0.0.1 for queries.


