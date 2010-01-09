<div style='margin:auto; text-align: center; margin-top: 0px; margin-bottom: 10px;'>
  <b class='rounded'>
  <b class='rounded1'></b>
  <b class='rounded2'></b>
  <b class='rounded3'></b>
  <b class='rounded4'></b>
  <b class='rounded5'></b></b>
  <div class='roundedfg' style='padding: 0px 5px;'>
  <div style='margin: auto; text-align: left; padding: 2px 5px; padding-left: 11px; clear: both; display:block; height:23px;'>

<form method="post" action="">
  <label><strong>Descr</strong>
    <input type="text" name="string" id="string" value="<?php  echo($_POST['string']); ?>" />
  </label>
  <label>
    <strong>Part No</strong>
    <select name="part" id="part">
      <option value="">All Parts</option>
      <?php
        $query = mysql_query("SELECT `entPhysicalModelName` FROM `entPhysical` GROUP BY `entPhysicalModelName` ORDER BY `entPhysicalModelName`");
        while($data = mysql_fetch_array($query)) {
          echo("<option value='".$data['entPhysicalModelName']."'");
          if($data['entPhysicalModelName'] == $_POST['part']) { echo("selected"); }
          echo(">".$data['entPhysicalModelName']."</option>");
        }
      ?>
    </select>
  </label>
  <label><strong>Serial</strong>
    <input type="text" name="serial" id="serial" value="<?php  echo($_POST['serial']); ?>" />
  </label>
  <label>
    <strong>Device</strong>
    <select name="device" id="device">
      <option value="">All Devices</option>
      <?php
        $query = mysql_query("SELECT * FROM `devices` ORDER BY `hostname`");
        while($data = mysql_fetch_array($query)) {
          echo("<option value='".$data['device_id']."'");

          if($data['device_id'] == $_POST['device']) { echo("selected"); }

          echo(">".$data['hostname']."</option>");
        }
      ?>
    </select>
  </label>
  <input type="text" size=24 name="device_string" id="device_string" value="<?php  echo($_POST['device_string']); ?>" />
  <input style type=submit value=Search>

</form>
</div>
</div>
  <b class='rounded'>
  <b class='rounded5'></b>
  <b class='rounded4'></b>
  <b class='rounded3'></b>
  <b class='rounded2'></b>
  <b class='rounded1'></b></b>
</div>


<?php

if($_POST['string']) {
  $where .= " AND E.entPhysicalDescr LIKE '%".$_POST['string']."%'";
}

if($_POST['device_string']) {
  $where .= " AND D.hostname LIKE '%".$_POST['device_string']."%'";
}

if($_POST['part']) {
  $where .= " AND E.entPhysicalModelName = '".$_POST['part']."'";
}

if($_POST['serial']) {
  $where .= " AND E.entPhysicalSerialNum LIKE '%".$_POST['serial']."%'";
}

if($_POST['device']) {
  $where .= " AND D.device_id = '".$_POST['device']."'";
}

if($_SESSION['userlevel'] >= '5') {
  $sql = "SELECT * from entPhysical AS E, devices AS D WHERE E.device_id = D.device_id $where ORDER BY D.hostname";
} else { 
  $sql = "SELECT * from entPhysical AS E, devices AS D, devices_perms AS P 
          WHERE E.device_id = D.device_id AND D.device_id = P.device_id $where ORDER BY D.hostname";
}

$query = mysql_query($sql);
echo("<table cellspacing=0 cellpadding=2 width=100%>");

echo("<tr><th>Hostname</th><th>Description</th><th>Name</th><th>Part No</th><th>Serial No</th></tr>");

while($entry = mysql_fetch_array($query)) { 
if($bg == $list_colour_a) { $bg = $list_colour_b; } else { $bg=$list_colour_a; }
echo("<tr style=\"background-color: $bg\"><td>" . generatedevicelink($entry, shortHost($entry['hostname'])) . "</td><td>" . $entry['entPhysicalDescr']  . 
     "</td><td>" . $entry['entPhysicalName']  . "</td><td>" . $entry['entPhysicalModelName']  . "</td><td>" . $entry['entPhysicalSerialNum'] . "</td></tr>");

}
echo("</table>");

?>
</table>

