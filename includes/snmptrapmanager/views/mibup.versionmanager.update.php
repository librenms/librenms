<!-- requires mibup.css.img view -->
<h3>MIB Version Update Results:</h3>
<ul>
<?php
$sList = '';
foreach ($aMessages as $r) {
    $sliclass = 'ok';
    if ($r[0] != 0) {
        $sliclass = 'error';
    }
    $sList .= '<li><img class="status '.$sliclass.'" /> ';
    switch ($r[0]) {
        case 0:
            if ($r[2] >= 0) {
                $sList .= $r[1] . ' updated to version ' . $r[2];
            } else {
                $sList .= $r[1] . ' deleted';
            }
            break;
        case 1:
            $sList .= $r[1] . ': ' . $r[2];
            break;
    }
    $sList .= '</li>';
}
echo $sList;
?>
</ul>
<br />
<p>Version update terminated.</p>
<p><a href="/snmptrapmanager/?mode=snmptt">Generate SNMPTT Configuration</a></p>
