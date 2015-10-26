# Oxidized integration

You can integrate LibreNMS with [Oxidized](https://github.com/ytti/oxidized-web) in two ways:

### Config viewing

This is a straight forward use of Oxidized, it relies on you having a working Oxidized setup which is already taking config snapshots for your devices. 
When you have that, you only need the following config to enable the display of device configs within the device page itself:

```php
$config['oxidized']['enabled']         = TRUE;
$config['oxidized']['url']             = 'http://127.0.0.1:8888';
```

We also support config versioning within Oxidized, this will allow you to see the old configs stored.

```php
$config['oxidized']['features']['versioning'] = true;
```

### Feeding Oxidized

Oxidized has support for feeding devices into it via an API call, support for Oxidized has been added to the LibreNMS API. A sample config for Oxidized is provided below.

You will need to configure default credentials for your devices, LibreNMS doesn't provide login credentials at this time.

```bash
      source:
        default: http
        debug: false
        http:
          url: https://librenms/api/v0/oxidized
          scheme: https
          delimiter: !ruby/regexp /:/
          map:
            name: hostname
            model: os
          headers:
            X-Auth-Token: '01582bf94c03104ecb7953dsadsadwed'
```

If you have devices which you do not wish to appear in Oxidized then you can edit those devices in Device -> Edit -> Misc and enable "Exclude from Oxidized?"
