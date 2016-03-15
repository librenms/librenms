# Billing Module

To enable and use the billing module you need to perform the following steps:

Edit `config.php` and add (or enable) the following line near the end of the config

```php
$config['enable_billing'] = 1; # Enable Billing
```

Edit `/etc/cron.d/librenms` and add the following:

```bash
*/5 * * * * librenms /opt/librenms/poll-billing.php >> /dev/null 2>&1
01  * * * * librenms /opt/librenms/billing-calculate.php >> /dev/null 2>&1
```

Create billing graphs as required.

## Options

Billing data is stored in the MySQL database, and you may wish to purge the detailed 
stats for old data (per-month totals will always be kept).  To enable this, add the 
following to `config.php`:

```php
$config['billing_data_purge'] = 12;     // Number of months to retain
```

Data for the last complete billing cycle will always be retained - only data older than
this by the configured number of months will be removed.  This task is performed in the
daily cleanup tasks.