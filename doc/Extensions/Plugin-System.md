source: Extensions/Plugin-System.md
path: blob/master/doc/

# Developing for the Plugin System

The plugin system is intended to integrate individual extensions into
LibreNMS that cannot be included in the project due to dependencies
or special benefits.

This documentation will give you a basis for how to write a plugin for LibreNMS based on Laravel.
A Example plugin is included in LibreNMS distribution which shows you your host notes
in the device overview and it supports the Markdown syntax.

# Generic structure

Plugins need to be installed into html/plugins

The structure of a plugin is follows:

```
app/Plugins
			/PluginName
					   /DeviceOverview.php [implements DeviceOverviewHook]
   					   /Menu.php           [implements MenuHook]
   					   /PortTab.php        [implements PortTabHook]
   					   /Settings.php       [implements SettingsHook]
					   /resources/views
									   /menu.blade.php            [called by menuHook]
									   /settings.blade.php        [called by SettingsHook]
									   /device-overview.blade.php [called by deviceHook]
									   /port-tab.blade.php        [called by portTabHook]
```

The above structure is checked before a plugin can be installed.

The files / folder names are case sensitive and must match.

PluginName - This is a directory and needs to be named as per the
plugin you are creating.


# Extend the Device Overview

```
<?php

namespace App\Plugins\ExamplePlugin;

use App\Plugins\Hooks\DeviceOverviewHook;

class DeviceOverview extends DeviceOverviewHook
{
}
?>
```

# Extend the Port Tabs

```
<?php

namespace App\Plugins\ExamplePlugin;

use App\Plugins\Hooks\PortTabHook;

class PortTab extends PortTabHook
{
}

```


# Add Settings to the Plugin

```
namespace App\Plugins\ExamplePlugin;

use App\Plugins\Hooks\SettingsHook;

class Settings extends SettingsHook
{
}
```

# Extend with own Pages

```
<?php

namespace App\Plugins\ExamplePlugin;

use App\Plugins\Hooks\MenuEntryHook;

class Menu extends MenuEntryHook
{
}

```


# More extentions

- If you need other reactions of these hooks you can overwrite the methods in your own class.
- If you like to create additional hooks you are welcome to add them. Please push it back to the community!
