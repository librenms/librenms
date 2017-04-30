<?php
echo '<tr class="list">';
echo '<td class="list">';
    echo $vm['vmid'];
echo '</td>';

echo '<td class="list">';
    echo $vm['description'];
echo '</td>';

if ($vm['status'] == 'stopped') {
    echo '<td class="list"><span style="min-width:40px; display:inline-block;" class="label label-default">OFF</span></td>';
} elseif ($vm['status'] == 'running') {
    echo '<td class="list"><span style="min-width:40px; display:inline-block;" class="label label-success">ON</span></td>';
} else {
    echo '<td class="list"><span style="min-width:40px; display:inline-block;" class="label label-default">?</span></td>';
}

if ($vm['status'] == 'running') {
    echo '<td class="list">';
    if (!empty($vm['cluster'])) {
        echo $vm['cluster'];
    }
    echo '</td>';
    echo '<td class="list">';
    if (!empty($vm['vmpid'])) {
        echo $vm['vmpid'];
    }
    echo '</td>';
    echo '<td class="list">';
    if (!empty($vm['vmramcurr'])) {
        echo $vm['vmramcurr'];
        echo " MB";
    }
    echo '</td>';
    echo '<td class="list">';
    if (!empty($vm['vmrammax'])) {
        echo $vm['vmrammax'];
        echo " MB";
    }
    echo '</td>';
    echo '<td class="list">';
    if (isset($vm['vmramuse'])) {
        echo $vm['vmramuse'];
        echo " %";
    }
    echo '</td>';
    echo '<td class="list">';
    if (isset($vm['vmcpu'])) {
        echo $vm['vmcpu'];
        echo " %";
    }
    echo '</td>';
    echo '<td class="list">';
    if (!empty($vm['vmstorage'])) {
         echo $vm['vmstorage'];
         echo " %";
    }
    echo '</td>';
} else {
    echo '<td class="list"></td>';
    echo '<td class="list"></td>';
    echo '<td class="list"></td>';
    echo '<td class="list"></td>';
    echo '<td class="list"></td>';
}

/*
if ($vm['vmwVmGuestOS'] == 'E: tools not installed') {
    echo '<td class="box-desc">Unknown (VMware Tools not installed)</td>';
} elseif ($vm['vmwVmGuestOS'] == '') {
    echo '<td class="box-desc"><i>(Unknown)</i></td>';
} elseif (isset($config['vmware_guestid'][$vm['vmwVmGuestOS']])) {
    echo '<td class="list">'.$config['vmware_guestid'][$vm['vmwVmGuestOS']].'</td>';
} else {
    echo '<td class="list">'.$vm['vmwVmGuestOS'].'</td>';
}

if ($vm['vmwVmMemSize'] >= 1024) {
    echo ('<td class=list>'.sprintf('%.2f', ($vm['vmwVmMemSize'] / 1024)).' GB</td>');
} else {
    echo '<td class=list>'.sprintf('%.2f', $vm['vmwVmMemSize']).' MB</td>';
}

echo '<td class="list">'.$vm['vmwVmCpus'].' CPU</td>';
*/
