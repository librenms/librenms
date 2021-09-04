source: Extensions/Plugin-System.md
path: blob/master/doc/

# Developing for the Plugin System

With plugins you can extend LibreNMS with special functions that are
specific to your setup or are not relevant or interesting for all community members.

You are able to intervene in defined places in the behavior of
the website, without it coming to problems with future updates.

This documentation will give you a basis for writing a plugin for
LibreNMS. An example plugin is included in the LibreNMS distribution.


# Version 2 Plugin System structure

Plugins in version 2 need to be installed into app/Plugins

The structure of a plugin is follows:

```
app/Plugins
            /PluginName
                       /DeviceOverview.php
                       /Menu.php
                       /Page.php
                       /PortTab.php
                       /Settings.php
                       /resources/views
                                       /device-overview.blade.php
                                       /menu.blade.php
                                       /page.blade.php
                                       /port-tab.blade.php
                                       /settings.blade.php
```

The above structure is checked before a plugin can be installed.

All file/folder names are case sensitive and must match the structure.

Only the blade files that are really needed need to be created. A plugin manager
will then load a hook that has a basic functionality.

If you want to customize the basic behavior of the hooks, you can create a
class in 'app/Plugins/PluginName' and overload the hook methods.

- device-overview.blade.php :: This is called in the Device
  Overview page. You receive the $device as a object per default, you can do your
  work here and display your results in a frame.

```
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default panel-condensed">
            <div class="panel-heading">
                <strong>{{ $title }}</strong>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-12">
                         {{ $device->hostname }}
		    	 <!-- Do you stuff here -->
                    </div>
		</div>
	    </div>
	</div>
    </div>
</div>
```

- port-tab.blade.php :: This is called in the Port page,
  in the "Plugins" menu_option that will appear when your plugin gets
  enabled. In this blade, you can do your work and display your
  results in a frame.

- menu.blade.php :: For a menu entry 

- page.blade.pho :: Here is a good place to add a own LibreNMS page without dependence with a device. A good place to create your own lists with special requirements and behavior.

- settings.blade.php :: If you need your own settings and variables, you can have a look in the ExamplePlugin.



If you want to change the behavior, you can customize the hooks methods. Just as an example, you could imagine that the device-overview.blade.php should only be displayed when the device is in maintanence mode. Of course the method is more for a permission concept but it gives you the idea.

```
abstract class DeviceOverviewHook
{
    ...
    public function authorize(User $user, Device $device, array $settings): bool
    {
        return $device->isUnderMaintenance();
    }
    ...
```

# Version 1 Plugin System structure (legacy verion)

Plugins need to be installed into html/plugins

The structure of a plugin is follows:

```
html/plugins
            /PluginName
                       /PluginName.php
                       /PluginName.inc.php
```

The above structure is checked before a plugin can be installed.

All files / folder names are case sensitive and must match.

PluginName - This is a directory and needs to be named as per the
plugin you are creating.

- PluginName.php :: This file is used to process calls into the plugin
  from the main LibreNMS install. Here only functions within the class
  for your plugin that LibreNMS calls will be executed. For a list of
  currently enabled system hooks, please see further down. The minimum
  code required in this file is (replace Test with the name of your
  plugin):

```
<?php

class Test {
}

?>
```

- PluginName.inc.php :: This file is the main included file when
                     browsing to the plugin itself. You can use this
                     to display / edit / remove whatever you like. The
                     minimum code required in this file is:

```
<?php

?>
```

# System Hooks

System hooks are called as functions within your plugin class. The
following system hooks are currently available:

- menu() :: This is called to build the plugin menu system and you
   can use this to link to your plugin (you don't have to).

```
    public static function menu() {
        echo('<li><a href="plugin/p='.get_class().'">'.get_class().'</a></li>');
    }
```

- device_overview_container($device) :: This is called in the Device
  Overview page. You receive the $device as a parameter, can do your
  work here and display your results in a frame.

```
    public static function device_overview_container($device) {
        echo('<div class="container-fluid"><div class="row"> <div class="col-md-12"> <div class="panel panel-default panel-condensed"> <div class="panel-heading"><strong>'.get_class().' Plugin </strong> </div>');
        echo('  Example plugin in "Device - Overview" tab <br>');
        echo('</div></div></div></div>');
    }
```

- port_container($device, $port) :: This is called in the Port page,
  in the "Plugins" menu_option that will appear when your plugin gets
  enabled. In this function, you can do your work and display your
  results in a frame.

```
    public static function port_container($device, $port) {
        echo('<div class="container-fluid"><div class="row"> <div class="col-md-12"> <div class="panel panel-default panel-condensed"> <div class="panel-heading"><strong>'.get_class().' plugin in "Port" tab</strong> </div>');
        echo ('Example display in Port tab</br>');
        echo('</div></div></div></div>');
    }
```
