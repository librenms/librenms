<script class="code" type="text/javascript">
function ToggleLogon($) {
  if ( document.getElementById('public-logon').style.display=="block" )
  {
    document.getElementById('public-logon').style.display="none";
    document.getElementById('public-status').style.display="block";
    //document.getElementById($id+"-div").innerHTML="click to expand";
  }
  else
  {
    document.getElementById('public-logon').style.display="block";
    document.getElementById('public-status').style.display="none";
    //document.getElementById($id+"-div").innerHTML="hide";
  }
};
</script>

<?php

// Set Defaults here

if(!isset($vars['format'])) { $vars['format'] = "list_detail"; }

$sql_param = array();
if(isset($vars['state']))
{
  if($vars['state'] == 'up')
  {
    $state = '1';
  }
    elseif($vars['state'] == 'down')
  {
    $state = '0';
  }
}

if ($vars['hostname']) { $where .= " AND hostname LIKE ?"; $sql_param[] = "%".$vars['hostname']."%"; }
if ($vars['os'])       { $where .= " AND os = ?";          $sql_param[] = $vars['os']; }
if ($vars['version'])  { $where .= " AND version = ?";     $sql_param[] = $vars['version']; }
if ($vars['hardware']) { $where .= " AND hardware = ?";    $sql_param[] = $vars['hardware']; }
if ($vars['features']) { $where .= " AND features = ?";    $sql_param[] = $vars['features']; }
if ($vars['type'])     { $where .= " AND type = ?";        $sql_param[] = $vars['type']; }
if ($vars['state'])    {
  $where .= " AND status= ?";       $sql_param[] = $state;
  $where .= " AND disabled='0' AND `ignore`='0'"; $sql_param[] = '';
}
if ($vars['disabled']) { $where .= " AND disabled= ?";     $sql_param[] = $vars['disabled']; }
if ($vars['ignore'])   { $where .= " AND `ignore`= ?";       $sql_param[] = $vars['ignore']; }

$pagetitle[] = "Public Devices";

//print_optionbar_end();

echo '<div class="well"><h3>System Status<button class="btn btn-default" type="submit" style="float:right;" onclick="ToggleLogon()">Logon</button></h3></div>';

$query = "SELECT * FROM `devices` WHERE 1 ".$where." ORDER BY hostname";

list($format, $subformat) = explode("_", $vars['format']);

if($format == "graph")
{
  $row = 1;
  foreach (dbFetchRows($query, $sql_param) as $device)
  {
    if (is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

    if (device_permitted($device['device_id']))
    {
      if (!$location_filter || ((get_dev_attrib($device,'override_sysLocation_bool') && get_dev_attrib($device,'override_sysLocation_string') == $location_filter)
        || $device['location'] == $location_filter))
      {
        $graph_type = "device_".$subformat;

        if ($_SESSION['widescreen']) { $width=270; } else { $width=315; }

        echo("<div style='display: block; padding: 1px; margin: 2px; min-width: ".($width+78)."px; max-width:".($width+78)."px; min-height:170px; max-height:170px; text-align: center; float: left; background-color: #f5f5f5;'>
        <a href='device/device=".$device['device_id']."/' onmouseover=\"return overlib('\
        <div style=\'font-size: 16px; padding:5px; font-weight: bold; color: #e5e5e5;\'>".$device['hostname']." - ".$interface['ifDescr']."</div>\
        <img src=\'graph.php?type=$graph_type&amp;device=".$device['device_id']."&amp;from=".$config['time']['day']."&amp;to=".$config['time']['now']."&amp;width=450&amp;height=150&amp;title=yes\'>\
        ', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >".
        "<img src='graph.php?type=$graph_type&amp;device=".$device['device_id']."&amp;from=".$config['time']['day']."&amp;to=".$config['time']['now']."&amp;width=".$width."&amp;height=110&amp;legend=no&amp;title=yes'>
        </a>
        </div>");
      }
    }
  }

} else {

  echo('<div class="panel panel-default panel-condensed">
          <div class="table-responsive">
          <table class="table table-condensed">');
  if ($subformat == "detail" || $subformat == "basic")
  {
    echo('<tr>
    <th></th>
    <th></th>
    <th>Device</th>
    <th></th>
    <th>Platform</th>
<!--    <th>Operating System</th> -->
    <th>Uptime/Location</th>
  </tr>');
  }

  foreach (dbFetchRows($query, $sql_param) as $device)
  {
    if (!device_permitted($device['device_id']))
    {
          include("includes/hostbox-public.inc.php");
    }
  }
  echo("</table>");
  echo('</div>');
  echo('</div>');
}

?>
<footer>
<div class="container">
<div class="row">
<div class="col-md-12 text-center">
<?php
echo(' <br /> <br /> ' . (isset($config['footer']) ? $config['footer'] : ''));
echo(' <br />Powered by <a href="' . $config['project_url'] . '" target="_blank">' . $config['project_name_version'].'</a>. ');
echo( $config['project_name'].' is <a href="http://www.gnu.org/philosophy/free-sw.html">Free Software</a>, released under the <a href="http://www.gnu.org/copyleft/gpl.html">GNU GPLv3</a>.<br/>');
echo(' Copyright &copy; 2006-2012 by Adam Armstrong. Copyright &copy; 2013-'.date("Y").' by the '.$config['project_name'].' Contributors.');
?>
</div>
</div>
</div>
</footer>
