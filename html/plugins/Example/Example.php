<?php

namespace LibreNMS\Plugins;

/**
 * We have developed some hooks that you can use simply by uncomment the respective line.
 * This will be sufficient in most cases and you can take care of the rendering. If you
 * have special requirements you can take the static methods of the respective hooks from
 * the sources of \LibreNMS\Plugins\ as a template and customize them.
 */
class Example extends Plugin
{
    //use DeviceHook;
    //use PortHook;
    use SettingsHook;
}
