<?php

### Set Defaults here

if(!isset($vars['format'])) { $vars['format'] = "list_basic"; }

print_optionbar_start();

if($vars['searchbar'] != "hide")
{

?>
<table style="text-align: left;" cellpadding=0 cellspacing=5 class=devicetable width=100%>
  <tr style='padding: 0px;'>
  <form method='post' action=''>
    <td width='200'>
      <select name='device_id' id='device_id' style='width: 180px;'>
        <option value=''>All Devices</option>
<?php

foreach (dbFetchRows("SELECT `device_id`,`hostname` FROM `devices` GROUP BY `hostname` ORDER BY `hostname`") as $data)
{
  echo('        <option value="'.$data['device_id'].'"');
  if ($data['device_id'] == $vars['device_id']) { echo("selected"); }
  echo(">".$data['hostname']."</option>");
}
?>
      </select>
      <br />
      <input type="hostname" name="hostname" id="hostname" title="Hostname" style='width: 180px;' <?php if (strlen($vars['hostname_text'])) {echo('value="'.$vars['hostname'].'"');} ?> />
    </td>
    <td width="120">
      <select name="state" id="state" style="width: 100px;">
        <option value="">All States</option>
        <option value="up" <?php if ($vars['state'] == "up") { echo("selected"); } ?>>Up</option>
        <option value="down"<?php if ($vars['state'] == "down") { echo("selected"); } ?>>Down</option>
        <option value="admindown" <?php if ($vars['state'] == "admindown") { echo("selected"); } ?>>Shutdown</option>
      </select>
      <br />

      <select name="ifSpeed" id="ifSpeed" style="width: 100px;">
      <option value="">All Speeds</option>
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
    <td width=170>
      <select name="ifType" id="ifType" style="width: 150px;">
        <option value="">All Media</option>
<?php
foreach (dbFetchRows("SELECT `ifType` FROM `ports` GROUP BY `ifType` ORDER BY `ifType`") as $data)
{
  if ($data['ifType'])
  {
    echo('        <option value="'.$data['ifType'].'"');
    if ($data['ifType'] == $vars['ifType']) { echo("selected"); }
    echo(">".$data['ifType']."</option>");
  }
}
?>
       </select>
<br />
      <select name="port_descr_type" id="port_descr_type" style="width: 150px;">
        <option value="">All Port Types</option>
<?php
foreach (dbFetchRows("SELECT `port_descr_type` FROM `ports` GROUP BY `port_descr_type` ORDER BY `port_descr_type`") as $data)
{
  if ($data['port_descr_type'])
  {
    echo('        <option value="'.$data['port_descr_type'].'"');
    if ($data['port_descr_type'] == $vars['port_descr_type']) { echo("selected"); }
    echo(">".ucfirst($data['port_descr_type'])."</option>");
  }
}
?>
         </select>
       </td>
       <td>
       </td>
       <td width="220">
        <input style="width: 200px;" title="Port Description" type="text" name="ifAlias" id="ifAlias" <?php if (strlen($vars['ifAlias'])) {echo('value="'.$vars['ifAlias'].'"');} ?> />
        <select style="width: 200px;" name="location" id="location">
          <option value="">All Locations</option>
          <?php
           ### fix me function?

           foreach (getlocations() as $location) ## FIXME function name sucks maybe get_locations ?
           {
             if ($location)
             {
               echo('<option value="'.$location.'"');
               if ($location == $vars['location']) { echo(" selected"); }
               echo(">".$location."</option>");
             }
           }
         ?>
        </select>
      </td>

      <td width=80>
        <label for="ignore">
        <input type=checkbox id="ignore" name="ignore" value=1 <?php if ($vars['ignore']) { echo("checked"); } ?> ></input> Ignored
        </label>
        <br />
        <label for="disable">
        <input type=checkbox id="disable" name="disable" value=1 <?php if ($vars['disable']) { echo("checked"); } ?> > Disabled</input>
        </label>
        <br />
        <label for="deleted">
        <input type=checkbox id="deleted" name="deleted" value=1 <?php if ($vars['deleted']) { echo("checked"); } ?> > Deleted</input>
        </label>
        </td>
        <td width=120>
        <select name="sort" id="sort" style="width: 110px;">
          <option value="">Host & Port Name</option>
          <option value="traffic"  <?php if ($vars['sort'] == "traffic")  { echo("selected"); } ?>>Traffic</option>
          <option value="traffic_in"  <?php if ($vars['sort'] == "traffic_in")  { echo("selected"); } ?>>Traffic In</option>
          <option value="traffic_out" <?php if ($vars['sort'] == "traffic_out") { echo("selected"); } ?>>Traffic Out</option>
          <option value="packets"  <?php if ($vars['sort'] == "packets")  { echo("selected"); } ?>>Packets</option>
          <option value="packets_in"  <?php if ($vars['sort'] == "packets_in")  { echo("selected"); } ?>>Packets In</option>
          <option value="packets_out" <?php if ($vars['sort'] == "packets_out") { echo("selected"); } ?>>Packets Out</option>
          <option value="errors"  <?php if ($vars['sort'] == "errors")  { echo("selected"); } ?>>Errors</option>
        </select>
        </td>
        <td style="text-align: center;" width=50>
        <input style="align:right; padding: 10px;" type=submit class=submit value=Search></div>
        <br />
        <a href="<?php echo(generate_url(array('page' => 'ports', 'section' => $vars['section'], 'bare' => $vars['bare']))); ?>" title="Reset critera to default." >Reset</a>
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

$menu_options = array('bits' => 'Bits',
                      'upkts' => 'Unicast Packets',
                      'nupkts' => 'Non-Unicast Packets',
                      'errors' => 'Errors');

$sep = "";
foreach ($menu_options as $option => $text)
{
  echo($sep);
  if ($vars['format'] == 'graph_'.$option)
  {
    echo('<span class="pagemenu-selected">');
  }
  echo('<a href="' . generate_url($vars, array('format' => 'graph_'.$option)) . '">' . $text . '</a>');
  if ($vars['format'] == 'graph_'.$option)
  {
    echo("</span>");
  }
  $sep = " | ";
}

echo('<div style="float: right;">');
?>

  <a href="<?php echo(generate_url($vars)); ?>" title="Update the browser URL to reflect the search criteria." >Update URL</a> |

<?php
  if ($vars['searchbar'] == "hide")
  {
    echo('<a href="'. generate_url($vars, array('searchbar' => '')).'">Search</a>');
  } else {
    echo('<a href="'. generate_url($vars, array('searchbar' => 'hide')).'">Search</a>');
  }

  echo("  | ");

  if ($vars['bare'] == "yes")
  {
    echo('<a href="'. generate_url($vars, array('bare' => '')).'">Header</a>');
  } else {
    echo('<a href="'. generate_url($vars, array('bare' => 'yes')).'">Header</a>');
  }

echo('</div>');

print_optionbar_end();

$param = array();

# FIXME block below is not totally used, at least the iftype stuff is bogus?
#if ($vars['status'] == "down" || $_GET['type'] == "down" || $vars['state'] == "down")
#{
#  $where .= "AND I.ifAdminStatus = 'up' AND I.ifOperStatus = 'down' AND I.ignore = '0'";
#}
#if ($_GET['optb'] == "admindown" || $_GET['type'] == "admindown" || $vars['state'] == "admindown") {
#  $where .= "AND I.ifAdminStatus = 'down'";
#}
#if ($_GET['optb'] == "errors" || $_GET['type'] == "errors" || $vars['state'] == "errors") {
#  $where .= "AND (I.`ifInErrors_delta` > '0' OR I.`ifOutErrors_delta` > '0')";
#}
#if ($vars['state'] == "up") {
#  $where .= "AND I.ifOperStatus = 'up'";
#}

if(!isset($vars['ignore']))   { $vars['ignore'] = "0"; }
if(!isset($vars['disabled'])) { $vars['disabled'] = "0"; }
if(!isset($vars['deleted']))  { $vars['deleted'] = "0"; }

foreach($vars as $var => $value)
{
  if ($value != "")
  {
    switch ($var)
    {
      case 'hostname':
      case 'location':
        $where .= " AND D.$var LIKE ?";
        $param[] = "%".$value."%";
      case 'device_id':
      case 'deleted':
      case 'ignore':
      case 'disable':
      case 'ifSpeed':
        if (is_numeric($value))
        {
          $where .= " AND I.$var = ?";
          $param[] = $value;
        }
        break;
      case 'ifType':
        $where .= " AND I.$var = ?";
        $param[] = $value;
        break;
      case 'ifAlias':
      case 'port_descr_type':
        $where .= " AND I.$var LIKE ?";
        $param[] = "%".$value."%";
        break;
      case 'errors':
        if ($value == 1)
        {
          $where .= " AND (I.`ifInErrors_delta` > '0' OR I.`ifOutErrors_delta` > '0')";
        }
        break;
      case 'state':
        if ($value == "down")
        {
          $where .= "AND I.ifAdminStatus = ? AND I.ifOperStatus = ?";
          $param[] = "up";
          $param[] = "down";
        } elseif($value == "up") {
          $where .= "AND I.ifAdminStatus = ? AND I.ifOperStatus = ?";
          $param[] = "up";
          $param[] = "up";
        } elseif($value == "admindown") {
          $where .= "AND I.ifAdminStatus = ?";
          $param[] = "down";
        }
      break;
    }
  }
}

switch ($vars['sort'])
{
  case 'traffic':
    $query_sort = " ORDER BY (I.ifInOctets_rate+I.ifOutOctets_rate)";
    break;
  case 'traffic_in':
    $query_sort = " ORDER BY I.ifInOctets_rate";
    break;
  case 'traffic_out':
    $query_sort = " ORDER BY I.ifOutOctets_rate";
    break;
  case 'packets':
    $query_sort = " ORDER BY (I.ifInUcastPkts_rate+I.ifOutUcastPkts_rate)";
    break;
  case 'packets_in':
    $query_sort = " ORDER BY I.ifInUcastPkts_rate";
    break;
  case 'packets_out':
    $query_sort = " ORDER BY I.ifOutUcastPkts_rate";
    break;
  case 'errors':
    $query_sort = " ORDER BY (I.ifInErrors + I.ifOutErrors)";
    break;
  default:
    $query_sort = " ORDER BY D.hostname, I.ifIndex";
}

$query = "SELECT * FROM `ports` AS I, `devices` AS D WHERE I.device_id = D.device_id ".$where." ".$query_sort." DESC";

$row = 1;

list($format, $subformat) = explode("_", $vars['format']);

$ports = dbFetchRows($query, $param);

echo(count($ports));

if(file_exists('pages/ports/'.$format.'.inc.php'))
{
 include('pages/ports/'.$format.'.inc.php');
}

?>
