source: Extensions/Plugin-System.md
path: blob/master/doc/

# Developing for the Plugin System

The plugin system is intended to integrate individual extensions into
LibreNMS that cannot be included in the project due to dependencies
or special benefits.

This documentation will give you a basis for how to write a plugin for LibreNMS.
A Example plugin is included in LibreNMS distribution which shows you Your host notes
in the device overview and it supports the Markdown syntax.

# Generic structure

Plugins need to be installed into html/plugins

The structure of a plugin is follows:

```
html/plugins
			/PluginName
					   /PluginName.php
					   /PluginName.inc.php [deprecated]
					   /resources/views
									   /menu.blade.php            [called from SettingsHook]
									   /settings.blade.php        [called from SettingsHook]
									   /device_overview.blade.php [called from deviceHook]
									   /port.blade.php            [called from portHook]
```

The above structure is checked before a plugin can be installed.

All files / folder names are case sensitive and must match.

PluginName - This is a directory and needs to be named as per the
plugin you are creating.

- PluginName.php :: This file is used to process calls into the plugin
  from the main LibreNMS install. Here only functions within the class
  for your plugin that LibreNMS calls will be executed. For a list of
  currently enabled system hooks, please see further down. The minimum
  code required in this file is (replace Example with the name of your
  plugin):

```
<?php

class Example extends Plugin{
	//use DeviceHook;
	//use PortHook;
	//use SettingsHook;
}

?>
```

- PluginName.inc.php :: This file is deprecated. It's replaced from the settings hook.

# Predefined Hook templates (php Traits)

System hooks are called within your plugin class. You can
activate every hook seperate with the use function (you don't have to).
The Hooks are located in LibreNMS/Plugin.
The following system hooks are currently available:

## SettingsHook

- menuData() :: This is called to build the plugin menu system and you
can use this to link to your plugin settings it's predefined that it
renders the menu.blade.php to link to your settingsHook (you don't have to).

- settingsData() :: This static method is brings the data to the
settings.blade.php when browsing to the plugin itself.
You can use this to display / edit / remove whatever you like.

If you like to extend informations which are given to the blade
(you can overwrite the defaults of every hook this way). You can
for example create a collection of all devices. Now you
can render this collection in setting.blade.php.
This kind of manipulation is possible with every hook.

```
	public static function settingsData() {
		return [
			'devices' => Device::all(),
			'title' => self::className(),
		];
```

## deviceHook

- This is called in the Device Overview page.
You receive the $device as a object in the blade view.
You can do your work there and display your results in a frame.

## portHook

- This is called in the Port page, in the "Plugins" menu_option
that will appear when your plugin gets enabled. In the  port.blade.php
component, you can do your work and display your results in a frame.

# More extentions

- If you need other reactions of these hooks you can overwrite the static functions in your own class.
- If you like to create additional hooks you are welcome to add them. Please push it back to the community!
