# Adding new config options to WebUI

Adding support for users to update a new config option via the WebUI is now a lot easier for general options. This 
document shows you how to add a new config option and even section to the WebUI.

#### Update DB

Firstly you will need to add the config option to the database. Here's an example:

```sql
insert into config (config_name,config_value,config_default,config_descr,config_group,config_group_order,config_sub_group,config_sub_group_order,config_hidden,config_disabled) values ('alert.tolerance_window','','','Tolerance window in seconds','alerting',0,'general',0,'0','0');
```

This will determine the default config option for `$config['alert']['tolerance_window']`.

#### Update WebUI

If the sub-section you want to add the new config option already exists then update the relevant file within 
`html/pages/settings/` otherwise you will need to create the new sub-section page. Here's an example of this:

[Commit example](https://github.com/librenm/librenms/commit/c5998f9ee27acdac0c0f7d3092fc830c51ff684c)

```php
<?php

$no_refresh = true;

$config_groups = get_config_by_group('alerting');

$mail_conf = array(
    array('name'               => 'alert.tolerance_window',
          'descr'              => 'Tolerance window for cron',
          'type'               => 'text',
    ),
);

echo '
<div class="panel-group" id="accordion">
    <form class="form-horizontal" role="form" action="" method="post">
';

echo generate_dynamic_config_panel('Email transport',true,$config_groups,$mail_conf,'mail');

echo '
    </form>
</div>
';
```

And that should be it!
