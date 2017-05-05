<?php
echo '<tr class="list">';
echo '<td class="list">';
    echo $vm['vmid'];
echo '</td>';

echo '<td class="list">';
    echo $vm['description'];
echo '</td>';

if ($vm['vmstatus'] == 'stopped') {
    echo '<td class="list"><span style="min-width:40px; display:inline-block;" class="label label-default">OFF</span></td>';
} elseif ($vm['vmstatus'] == 'running') {
    echo '<td class="list"><span style="min-width:40px; display:inline-block;" class="label label-success">ON</span></td>';
} else {
    echo '<td class="list"><span style="min-width:40px; display:inline-block;" class="label label-default">?</span></td>';
}

if ($vm['vmstatus'] == 'running') {
    echo '<td class="list">';
    if (!empty($vm['cluster'])) {
        echo $vm['cluster'];
    }
    echo '</td>';
    echo '<td class="list">';
    if (!empty($vm['vmtype'])) {
        echo $vm['vmtype'];
    }
    echo '</td>';
    echo '<td class="list">';
    if (!empty($vm['vmcpus'])) {
        echo $vm['vmcpus'];
    }
    echo '</td>';
    echo '<td class="list">';
    if (!empty($vm['vmpid'])) {
        echo $vm['vmpid'];
    }
    echo '</td>';
    echo '<td class="list">';
    if (!empty($vm['vmmem'])) {
        echo $vm['vmmem'];
        echo " MB";
    }
    echo '</td>';
    echo '<td class="list">';
    if (!empty($vm['vmmaxmem'])) {
        echo $vm['vmmaxmem'];
        echo " MB";
    }
    echo '</td>';
    echo '<td class="list">';
    if (!empty($vm['vmmemuse'])) {
        echo $vm['vmmemuse'];
        echo " %";
    }
    echo '</td>';
    echo '<td class="list">';
    if (!empty($vm['vmdisk'])) {
         echo $vm['vmdisk'];
         echo " MB";
    }
    echo '</td>';
    echo '<td class="list">';
    if (!empty($vm['vmmaxdisk'])) {
         echo $vm['vmmaxdisk'];
         echo " MB";
    }
    echo '</td>';
    echo '<td class="list">';
    if (!empty($vm['vmdiskuse'])) {
         echo $vm['vmdiskuse'];
         echo " %";
    }
    echo '</td>';
} else {
    echo '<td class="list"></td>';
    echo '<td class="list"></td>';
    echo '<td class="list"></td>';
    echo '<td class="list"></td>';
    echo '<td class="list"></td>';
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
