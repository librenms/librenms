source: Extensions/Plugin-System.md
path: blob/master/doc/

# Developing for the Plugin System

This will most likely be deprecated in favour of adding the possible
extensions to the core code base.

This documentation will hopefully give you a basis for how to write a
plugin for LibreNMS. A test plugin is included in LibreNMS distribution.

# Generic structure

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
