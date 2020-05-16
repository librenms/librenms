## Configuration directory

You can edit configuration of LibreNMS by placing `*.php` files in the `config.d` folder. Let's say you want to edit the [WebUI settings](https://docs.librenms.org/Support/Configuration/#webui-settings). Create a file called for example `webui.php` with this content:

```php
<?php
$config['page_refresh'] = "300";
$config['webui']['default_dashboard_id'] = 0;
```

This configuration will be included in LibreNMS and will override the default values.
