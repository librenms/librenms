# Graylog integration

We have simple integration for Graylog, you will be able to view any logs from within LibreNMS that have been parsed by the syslog input from within 
Graylog itself. This includes logs from devices which aren't in LibreNMS still, you can also see logs for a specific device under the logs section 
for the device.

Graylog itself isn't included within LibreNMS, you will need to install this separately either on the same infrastructure as LibreNMS or as a totally 
standalone appliance.

Config is simple, here's an example:

```php
$config['graylog']['server'] = 'http://127.0.0.1';
$config['graylog']['port'] = 12900;
$config['graylog']['username'] = 'admin';
$config['graylog']['password'] = 'admin';
```
