<?php

### Set Defaults here

if(!isset($vars['format'])) { $vars['format'] = "list_basic"; }


if($vars['searchbar'] != "hide")
{

print_optionbar_start(); 

?>
<table style="text-align: left;" cellpadding=0 cellspacing=5 class=devicetable width=100%>
  <tr style='padding: 0px;'>
  <form method='post' action=''>
    <td width='200'>
      <select name='device_id' id='device_id'>
      <option value=''>All Devices</option>
<?php

foreach (dbFetchRows("SELECT `device_id`,`hostname` FROM `devices` GROUP BY `hostname` ORDER BY `hostname`") as $data)
{
  echo("<option value='".$data['device_id']."'");
  if ($data['device_id'] == $vars['device_id']) { echo("selected"); }
  echo(">".$data['hostname']."</option>");
}
?>
      </select>
    </td>
    <td width='150'>
      <select name='state' id='state'>
        <option value=''>All States</option>
        <option value='up' <?php if ($vars['state'] == "up") { echo("selected"); } ?>>Up</option>
        <option value='down'<?php if ($vars['state'] == "down") { echo("selected"); } ?>>Down</option>
        <option value='admindown' <?php if ($vars['state'] == "admindown") { echo("selected"); } ?>>Shutdown</option>
        <option value='errors' <?php if ($vars['state'] == "errors") { echo("selected"); } ?>>Errors</option>
        <option value='ignored' <?php if ($vars['state'] == "ignored") { echo("selected"); } ?>>Ignored</option>
        <option value='ethernet' <?php if ($vars['state'] == "ethernet") { echo("selected"); } ?>>Ethernet</option>
        <option value='l2vlan' <?php if ($vars['state'] == "l2vlan") { echo("selected"); } ?>>L2 VLAN</option>
        <option value='sonet' <?php if ($vars['state'] == "sonet") { echo("selected"); } ?>>SONET</option>
        <option value='propvirtual' <?php if ($vars['state'] == "propvirtual") { echo("selected"); } ?>>Virtual</option>
        <option value='ppp' <?php if ($vars['state'] == "ppp") { echo("selected"); } ?>>PPP</option>
        <option value='loopback' <?php if ($vars['state'] == "loopback") { echo("selected"); } ?>>Loopback</option>
      </select>
    </td>
    <td width=110>
      <select name='ifSpeed' id='ifSpeed'>
      <option value=''>All Speeds</option>
<?php
foreach (dbFetchRows("SELECT `ifSpeed` FROM `ports` GROUP BY `ifSpeed` ORDER BY `ifSpeed`") as $data)
{
  if ($data['ifSpeed'])
  {
    echo("<option value='".$data['ifSpeed']."'");
    if ($data['ifSpeed'] == $vars['ifSpeed']) { echo("selected"); }
    echo(">".humanspeed($data['ifSpeed'])."</option>");
  }
}
?>
       </select>
    </td>
    <td width=200>
      <select name='ifType' id='ifType'>
      <option value=''>All Media</option>
<?php
foreach (dbFetchRows("SELECT `ifType` FROM `ports` GROUP BY `ifType` ORDER BY `ifType`") as $data)
{
  if ($data['ifType'])
  {
    echo("<option value='".$data['ifType']."'");
    if ($data['ifType'] == $vars['ifType']) { echo("selected"); }
    echo(">".$data['ifType']."</option>");
  }
}
?>
       </select>
             </td>
             <td>
        <input type="text" name="ifAlias" id="ifAlias" size=40 value="<?php echo($vars['ifAlias']); ?>" />
        Deleted <input type=checkbox id="deleted" name="deleted" value=1 <?php if ($vars['deleted']) { echo("checked"); } ?> ></input>
        </td>
        <td style="text-align: right;">
        <input style="align:right;" type=submit class=submit value=Search></div>
             </td>
      <td align=center>
      <a href="<?php echo(generate_url($vars)); ?>" title="Update the browser URL to reflect the search criteria." >Update URL</a> <br />
      <a href="<?php echo(generate_url(array('page' => 'monitor', 'section' => $vars['section'], 'bare' => $vars['bare']))); ?>" title="Reset critera to default." >Reset</a>
      </td>

           </form>
         </tr>
</table>
<hr />

<?php }

echo('<span style="font-weight: bold;">Lists</span> &#187; ');

$menu_options = array('basic'      => 'Basic',
                      'detail'     => 'Detail');

$sep = "";
foreach ($menu_options as $option => $text)
{
  echo($sep);
  if ($vars['format'] == "list_".$option)
  {
    echo("<span class='pagemenu-selected'>");
  }
  echo('<a href="' . generate_url($vars, array('format' => "list_".$option)) . '">' . $text . '</a>');
  if ($vars['format'] == "list_".$option)
  {
    echo("</span>");
  }
  $sep = " | ";
}
?>

 | 

<span style="font-weight: bold;">Graphs</span> &#187; 

<?php

$menu_options = array('bits' => 'Basic',
                      'upkts' => 'Unicast Packets',
                      'nupkts' => 'Non-Unicast Packets',
                      'errors' => 'Errors');

$sep = "";
foreach ($menu_options as $option => $text)
{
  echo($sep);
  if ($vars['format'] == 'graph_'.$option)
  {
    echo("<span class='pagemenu-selected'>");
  }
  echo('<a href="' . generate_url($vars, array('format' => 'graph_'.$option)) . '">' . $text . '</a>');
  if ($vars['format'] == 'graph_'.$option)
  {
    echo("</span>");
  }
  $sep = " | ";
}

echo('<div style="float: right;">');

  if($vars['searchbar'] == "hide")
  {
    echo('<a href="'. generate_url($vars, array('searchbar' => '')).'">Restore Search</a>');
  } else {
    echo('<a href="'. generate_url($vars, array('searchbar' => 'hide')).'">Remove Search</a>');
  }

  echo("  | ");

  if($vars['bare'] == "yes")
  {
    echo('<a href="'. generate_url($vars, array('bare' => '')).'">Restore Header</a>');
  } else {
    echo('<a href="'. generate_url($vars, array('bare' => 'yes')).'">Remove Header</a>');
  }


echo('</div>');


print_optionbar_end();

#if ($_SESSION['userlevel'] >= '5') {
#  $sql = "SELECT * FROM `ports` AS I, `devices` AS D WHERE I.device_id = D.device_id ORDER BY D.hostname, I.ifDescr";
#} else {
#  $sql = "SELECT * FROM `ports` AS I, `devices` AS D, `devices_perms` AS P WHERE I.device_id = D.device_id AND D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' ORDER BY D.hostname, I.ifDescr";
#}

$param = array();

# FIXME block below is not totally used, at least the iftype stuff is bogus?
if ($_GET['opta'] == "down" || $_GET['type'] == "down" || $vars['state'] == "down")
{
  $where .= "AND I.ifAdminStatus = 'up' AND I.ifOperStatus = 'down' AND I.ignore = '0'";
} elseif ($_GET['optb'] == "admindown" || $_GET['type'] == "admindown" || $vars['state'] == "admindown") {
  $where .= "AND I.ifAdminStatus = 'down'";
} elseif ($_GET['optb'] == "errors" || $_GET['type'] == "errors" || $vars['state'] == "errors") {
  $where .= "AND (I.`ifInErrors_delta` > '0' OR I.`ifOutErrors_delta` > '0')";
} elseif ($_GET['type'] == "up" || $vars['state'] == "up") {
  $where .= "AND I.ifOperStatus = 'up'";
} elseif ($_GET['optb'] == "ignored" || $_GET['type'] == "ignored" || $vars['state'] == "ignored") {
  $where .= "AND I.ignore = '1'";
} elseif ($_GET['type'] == "l2vlan" || $vars['state'] == "l2vlan") {
  $where .= " AND I.ifType = 'l2vlan'";
} elseif ($_GET['type'] == "ethernet" || $vars['state'] == "ethernet") {
  $where .= " AND I.ifType = 'ethernetCsmacd'";
} elseif ($_GET['type'] == "loopback" || $vars['state'] == "loopback") {
  $where .= " AND I.ifType = 'softwareLoopback'";
} elseif ($_GET['type'] == "sonet" || $vars['state'] == "sonet") {
  $where .= " AND I.ifType = 'sonet'";
} elseif ($vars['state'] == "propvirtual") {
  $where .= " AND I.ifType = 'propVirtual'";
} elseif ($vars['state'] == "ppp") {
  $where .= " AND I.ifType = 'ppp'";
}

if (is_numeric($vars['device_id'])) 
{ 
  $where .= " AND I.device_id = ?";
  $param[] = $vars['device_id'];
}
if ($vars['ifType']) 
{
  $where .= " AND I.ifType = ?"; 
  $param[] = $vars['ifType'];
}

if (is_numeric($vars['ifSpeed'])) 
{
  $where .= " AND I.ifSpeed = ?"; 
  $param[] = $vars['ifSpeed'];
}

if ($vars['ifAlias']) {
  $where .= " AND I.ifAlias LIKE ?"; 
  $param[] = "%".$vars['ifAlias']."%";
}

if ($vars['deleted'] || $_GET['type'] == "deleted") { $where .= " AND I.deleted = '1'";  }

$query = "SELECT * FROM `ports` AS I, `devices` AS D WHERE I.device_id = D.device_id ".$where." ORDER BY D.hostname, I.ifIndex";

$row = 1;

list($format, $subformat) = explode("_", $vars['format']);

if(file_exists('pages/ports/'.$format.'.inc.php'))
{
 include('pages/ports/'.$format.'.inc.php');
}


?>
