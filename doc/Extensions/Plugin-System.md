source: Extensions/Plugin-System.md
# Developing for the Plugin System

This documentation will hopefully give you a basis for how to write a plugin for LibreNMS.

A test plugin is available on GitHub: https://github.com/laf/Test

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

PluginName - This is a directory and needs to be named as per the plugin you are creating.

PluginName.php - This file is used to process calls into the plugin from the main LibreNMS install.
                 Here only functions within the class for your plugin that LibreNMS calls will be executed.
                 For a list of currently enabled system hooks, please see further down.
                 The minimum code required in this file is (replace Test with the name of your plugin):
```
<?php

class Test {
}

?>
```

PluginName.inc.php - This file is the main included file when browsing to the plugin itself.
                     You can use this to display / edit / remove whatever you like.
                     The minimum code required in this file is:
```
<?php

?>
```

### System Hooks ###

System hooks are called as functions within your plugin class, so for example to create a menu entry within the Plugin dropdown you would do:

```
  public function menu() {
    echo('<li><a href="plugin/p='.get_class().'">'.get_class().'</a></li>');
  }
```

This would then add the name and a link to your plugin.

The following system hooks are currently available:

menu()
* This is called to build the plugin menu system and you can use this to link to your plugin (you don't have to).
