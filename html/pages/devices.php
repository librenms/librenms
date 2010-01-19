<?php print_optionbar_start(52); ?>
<table cellpadding=4 cellspacing=0 class=devicetable width=100%>
<form method='post' action=''>
<tr> 
             <td width='30' align=center valign=middle></td>
             <td width='300'><span style='font-weight: bold; font-size: 14px;'></span>
             <input type="text" name="hostname" id="hostname" size=40 value="<?php  echo($_POST['hostname']); ?>" />
             </td>
             <td width='200'>
      <select name='os' id='os'>
      <option value=''>All OSes</option>
      <?php
        $query = mysql_query("SELECT `os` FROM `devices` GROUP BY `os` ORDER BY `os`");
        while($data = mysql_fetch_array($query)) {
          echo("<option value='".$data['os']."'");
          if($data['os'] == $_POST['os']) { echo("selected"); }
          echo(">".$data['os']."</option>");
        }
      ?>
       </select>
             <br />
      <select name='version' id='version'>
      <option value=''>All Versions</option>
      <?php
        $query = mysql_query("SELECT `version` FROM `devices` GROUP BY `version` ORDER BY `version`");
        while($data = mysql_fetch_array($query)) {
          echo("<option value='".$data['version']."'");
          if($data['version'] == $_POST['version']) { echo("selected"); }
          echo(">".$data['version']."</option>");
        }
      ?>
       </select>
             </td>
             <td width='200'>
      <select name='hardware' id='hardware'>
      <option value=''>All Platforms</option>
      <?php
        $query = mysql_query("SELECT `hardware` FROM `devices` GROUP BY `hardware` ORDER BY `hardware`");
        while($data = mysql_fetch_array($query)) {
          echo("<option value='".$data['hardware']."'");
          if($data['hardware'] == $_POST['hardware']) { echo("selected"); }
          echo(">".$data['hardware']."</option>");
        }
      ?>
       </select>
             <br />
      <select name='features' id='features'>
      <option value=''>All Featuresets</option>
      <?php
        $query = mysql_query("SELECT `features` FROM `devices` GROUP BY `features` ORDER BY `features`");
        while($data = mysql_fetch_array($query)) {
          echo("<option value='".$data['features']."'");
          if($data['features'] == $_POST['features']) { echo("selected"); }
          echo(">".$data['features']."</option>");
        }
      ?>
       </select>
             </td>
             <td>
      <select name='location' id='location'>
      <option value=''>All Locations</option>
      <?php
        $query = mysql_query("SELECT `location` FROM `devices` GROUP BY `location` ORDER BY `location`");
        while($data = mysql_fetch_array($query)) {
          echo("<option value='".$data['location']."'");
          if($data['location'] == $_POST['location']) { echo("selected"); }
          echo(">".$data['location']."</option>");
        }
      ?>
       </select>
         <input type=submit value=Search>

             </td>
             <td width=10>
             </td>
           </tr>
  </form>
</table>

<?php
print_optionbar_end();


if($_POST['hostname']) { $where = " AND hostname LIKE '%".$_POST['hostname']."%'"; }
if($_POST['os']) { $where = " AND os = '".$_POST['os']."'"; }
if($_POST['version']) { $where .= " AND version = '".$_POST['version']."'"; }
if($_POST['hardware']) { $where .= " AND hardware = '".$_POST['hardware']."'"; }
if($_POST['features']) { $where .= " AND features = '".$_POST['features']."'"; }
if($_POST['location']) { $where .= " AND location = '".$_POST['location']."'"; }
if($_GET['location']) { $where = "AND location = '$_GET[location]'"; }
if($_GET['location'] == "Unset") { $where = "AND location = ''"; }
if($_GET['type']) { $where = "AND type = '$_GET[type]'"; }

$sql = "select * from devices WHERE 1 $where ORDER BY `ignore`, `status`, `hostname`";
if($_GET['status'] == "alerted") { 
  $sql = "select * from devices " . $device_alert_sql . " GROUP BY `device_id` ORDER BY `ignore`, `status`, `os`, `hostname`";  
}

echo('<table cellpadding="7" cellspacing="0" class="devicetable" width="100%">
<tr class="tablehead"><th></th><th>Device</th><th>Operating System</th><th>Platform</th><th>Uptime</th></tr>');

$device_query = mysql_query($sql);
while($device = mysql_fetch_array($device_query)) {
  if( devicepermitted($device['device_id']) ) {
    include("includes/hostbox.inc");
  }
}

echo("</table>");

?>
