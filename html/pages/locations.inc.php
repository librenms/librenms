<?php

use App\Models\Location;

$pagetitle[] = 'Locations';

print_optionbar_start();

echo '<span style="font-weight: bold;">Locations</span> &#187; ';

$menu_options = array(
    'basic'   => 'Basic',
    'traffic' => 'Traffic',
);

if (!$vars['view']) {
    $vars['view'] = 'basic';
}

$sep = '';
foreach ($menu_options as $option => $text) {
    echo $sep;
    if ($vars['view'] == $option) {
        echo "<span class='pagemenu-selected'>";
    }

    echo '<a href="locations/view='.$option.'/">'.$text.'</a>';
    if ($vars['view'] == $option) {
        echo '</span>';
    }

    $sep = ' | ';
}

unset($sep);

print_optionbar_end();

echo '<table cellpadding="7" cellspacing="0" class="devicetable" width="100%">';

foreach (Location::hasAccess(Auth::user())->get() as $location) {
    /** @var Location $location */
    $num = $location->devices()->count();
    $net = $location->devices()->where('type', 'network')->count();
    $srv = $location->devices()->where('type', 'server')->count();
    $fwl = $location->devices()->where('type', 'firewall')->count();
    $hostalerts = $location->devices()->isDown()->count();

    if ($hostalerts) {
        $alert = '<i class="fa fa-flag" style="color:red" aria-hidden="true"></i>';
    } else {
        $alert = '';
    }

    if ($location != '') {
        echo '      <tr class="locations">
            <td class="interface" width="300"><a class="list-bold" href="devices/location='.$location->id.'/">'.display($location->location).'</a></td>
            <td width="100">'.$alert.'</td>
            <td width="100">'.$num.' devices</td>
            <td width="100">'.$net.' network</td>
            <td width="100">'.$srv.' servers</td>
            <td width="100">'.$fwl.' firewalls</td>
            </tr>
            ';

        if ($vars['view'] == 'traffic') {
            echo '<tr></tr><tr class="locations"><td colspan=6>';

            $graph_array['type']   = 'location_bits';
            $graph_array['height'] = '100';
            $graph_array['width']  = '220';
            $graph_array['to']     = $config['time']['now'];
            $graph_array['legend'] = 'no';
            $graph_array['id']     = $location->id;

            include 'includes/print-graphrow.inc.php';

            echo '</tr></td>';
        }

        $done = 'yes';
    }//end if
}//end foreach

echo '</table>';
