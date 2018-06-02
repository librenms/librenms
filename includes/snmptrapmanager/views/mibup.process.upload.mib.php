<!-- requires mibup.css.img view -->
<h3>MIB Upload results:</h3>
<ul>
<?php
$sList = '';
foreach ($aUpResults as $r) {
    $sliclass = 'ok';
    if ($r[0] != 0) {
        $sliclass = 'error';
    }
    $sList .= '<li><img class="status '.$sliclass.'" /> ';
    switch ($r[0]) {
        case 0:
            $sList .= $r[1];
            break;
        case 1:
            $sList .= $r[1] . ': ' . _('mib not uploaded');
            break;
        case 2:
            $sList .= $r[1] . ': ' . _('failed: ' . $r[2]);
            break;
    }
    $sList .= '</li>';
}
echo $sList;
?>
</ul>
<br />
<p>MIB Upload terminated.</p>
<p><a href="/snmptrapmanager/?mode=snmptt">Generate SNMPTT Configuration</a></p>
