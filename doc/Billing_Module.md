# Billing Module

To enable and use the billing module you need to perform the following steps:

Edit `config.php` and add (or enable) the following line near the end of the config

```php
$config['enable_billing'] = 1; # Enable Billing
```

Edit `/etc/cron.d/librenms` and add the following:

```bash
*/5 * * * * root /opt/librenms/poll-billing.php >> /dev/null 2>&1
01  * * * * root /opt/librenms/billing-calculate.php >> /dev/null 2>&1
```

Create billing graphs as required.
