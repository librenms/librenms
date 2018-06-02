<h3>MIB Version Manager</h3>
<form method="post" action="/snmptrapmanager/?mode=version_manager" style="margin-left: 20px;">
<p>Update current version of uploaded mibs:</p><br />
<input type="submit" name="form_versionmanager_update_version" />
<input type="reset" />
<label><input type="checkbox" name="form_versionmanager_update_latestversion" /> Update all MIBS to the latest available version.</label>
<br />
<?php
$aMIBNames = array();
$aSelects = array();
foreach ($aMIBList as $aMIB) {
    array_push($aMIBNames, '<label style="min-width: 250px; text-align: right; padding-right: 20px;">' . $aMIB['name'] . '</label> ');
    $sSelect = '<select style="height: 25px;" name="mibup_versionmanager_list_version[]">';
    foreach (array_reverse($aMIB['versions']) as $aVersion) {
        $sSelected = '';
        $iSelected = 0;
        if ($aMIB['current_version'] == $aVersion['version']) {
            $sSelected = 'selected';
            $iSelected = 1;
        }
        $iMaxVersion = $aMIB['versions'][count($aMIB['versions']) - 1]['version'];
        $sOptValue = $aMIB['id'] . '.' . $aVersion['version'] . '.' . $iSelected . '.' . $aMIB['name'];
        $sOptTxt = 'v' . $aVersion['version'] . '/' . $iMaxVersion . ' - ' . $aVersion['date'];
        $sSelect .= '<option value="' . $sOptValue . '" ' . $sSelected . '>' . $sOptTxt . '</option>';
    }
    $sSelect .= '<option value="' . $aMIB['id'] . '.-1.0.'.$aMIB['name'].'">delete all</option>';
    $sSelect .= '</select>';
    array_push($aSelects, $sSelect);
}
?>
<br />
<div id="mibup_mibversions">
        <div id="mibup_vm_mibnames">
    <?php
    $bFlip = false;
    foreach ($aMIBNames as $i => $sMIBName) {
        if ($bFlip) {
            $sColor = 'lightgrey';
        } else {
            $sColor = 'white';
        }
        echo '<div style="display: inline-block; background-color: '.$sColor.'">';
        echo $sMIBName;
        echo $aSelects[$i];
        echo '</div><br />';
        $bFlip = !$bFlip;
    }
    ?>
        </div>
</div>
<br />
<input type="submit" name="form_versionmanager_update_version" />
<input type="reset" />
</form>
