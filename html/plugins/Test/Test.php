<?php

namespace LibreNMS\Plugins;

class Test
{
    public static function menu()
    {
        echo '<li><a href="plugin/p=Test">Test</a></li>';
    }

    //end menu()

    public function device_overview_container($device)
    {
        echo '<div class="container-fluid"><div class="row"> <div class="col-md-12"> <div class="panel panel-default panel-condensed"> <div class="panel-heading"><strong>' . get_class() . ' Plugin </strong> </div>';
        echo '  Example plugin in "Device - Overview" tab <br>';
        echo '</div></div></div></div>';
    }

    public function port_container($device, $port)
    {
        echo '<div class="container-fluid"><div class="row"> <div class="col-md-12"> <div class="panel panel-default panel-condensed"> <div class="panel-heading"><strong>' . get_class() . ' plugin in "Port" tab</strong> </div>';
        echo 'Example display in Port tab</br>';
        echo '</div></div></div></div>';
    }
}
