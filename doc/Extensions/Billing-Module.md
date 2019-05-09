source: Extensions/Billing-Module.md
path: blob/master/doc/
# Billing Module

With the billing module you can create a bill, assign a quota to it and add ports to it.  
It then tracks the ports usage and shows you the usage in the bill, including any overage.  
Accounting by both total transferred data and 95th percentile is supported.

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

## Data Retention

Billing data is stored in the MySQL database, and you may wish to purge the detailed 
stats for old data (per-month totals will always be kept).  To enable this, add the 
following to `config.php`:

```php
$config['billing_data_purge'] = 12;     // Number of months to retain
```

Data for the last complete billing cycle will always be retained - only data older than
this by the configured number of months will be removed.  This task is performed in the
daily cleanup tasks.

## 95th Percentile Calculation

For 95th Percentile billing, the default behavior is to use the highest of the input 
or output 95th Percentile calculation.
To instead use the combined total of inout + output to derive the 95th percentile,
add the following the `config.php`:

```php
$config['billing_aggregate_95th'] = 1;  // Combine in + out throughput before calculating 95th
```

Note that the aggregate option changes how billing summaries are calculated and stored in the database.
Changing this setting will make any values already stored for the current billing cycle inaccurate.
