<?php

// Set Defaults here

if(!isset($vars['format'])) { $vars['format'] = "list_detail"; }

$sql_param = array();

if ($vars['hostname']) { $where .= " AND hostname LIKE ?"; $sql_param[] = "%".$vars['hostname']."%"; }
if ($vars['os'])       { $where .= " AND os = ?";          $sql_param[] = $vars['os']; }
if ($vars['version'])  { $where .= " AND version = ?";     $sql_param[] = $vars['version']; }
if ($vars['hardware']) { $where .= " AND hardware = ?";    $sql_param[] = $vars['hardware']; }
if ($vars['features']) { $where .= " AND features = ?";    $sql_param[] = $vars['features']; }
if ($vars['type'])     { $where .= " AND type = ?";        $sql_param[] = $vars['type']; }

if ($vars['location'] == "Unset") { $location_filter = ''; }
if ($vars['location']) { $location_filter = $vars['location']; }

$pagetitle[] = "Devices";

print_optionbar_start();

if($vars['searchbar'] != "hide")
{

?>
<form method="post" action="">
  <table cellpadding="4" cellspacing="0" class="devicetable" width="100%">
    <tr>
      <td width="290"><span style="font-weight: bold; font-size: 14px;"></span>
        <input type="text" name="hostname" id="hostname" size="38" value="<?php echo($vars['hostname']); ?>" />
      </td>
      <td width="200">
        <select name='os' id='os'>
          <option value=''>All OSes</option>
          <?php

foreach (dbFetch('SELECT `os` FROM `devices` AS D WHERE 1 GROUP BY `os` ORDER BY `os`') as $data)
{
  if ($data['os'])
  {
    echo("<option value='".$data['os']."'");
    if ($data['os'] == $vars['os']) { echo(" selected"); }
    echo(">".$config['os'][$data['os']]['text']."</option>");
  }
}
          ?>
        </select>
        <br />
        <select name='version' id='version'>
          <option value=''>All Versions</option>
          <?php

foreach (dbFetch('SELECT `version` FROM `devices` AS D WHERE 1 GROUP BY `version` ORDER BY `version`') as $data)
{
  if ($data['version'])
  {
    echo("<option value='".$data['version']."'");
    if ($data['version'] == $vars['version']) { echo(" selected"); }
    echo(">".$data['version']."</option>");
  }
}
          ?>
        </select>
      </td>
      <td width="200">
        <select name="hardware" id="hardware">
          <option value="">All Platforms</option>
          <?php
foreach (dbFetch('SELECT `hardware` FROM `devices` AS D WHERE 1 GROUP BY `hardware` ORDER BY `hardware`') as $data)
{
  if ($data['hardware'])
  {
    echo('<option value="'.$data['hardware'].'"');
    if ($data['hardware'] == $vars['hardware']) { echo(" selected"); }
    echo(">".$data['hardware']."</option>");
  }
}
          ?>
        </select>
        <br />
        <select name="features" id="features">
          <option value="">All Featuresets</option>
          <?php

foreach (dbFetch('SELECT `features` FROM `devices` AS D WHERE 1 GROUP BY `features` ORDER BY `features`') as $data)
{
  if ($data['features'])
  {
    echo('<option value="'.$data['features'].'"');
    if ($data['features'] == $vars['features']) { echo(" selected"); }
    echo(">".$data['features']."</option>");
  }
}
          ?>
        </select>
      </td>
      <td>
        <select name="location" id="location">
          <option value="">All Locations</option>
          <?php
// fix me function?

foreach (getlocations() as $location) // FIXME function name sucks maybe get_locations ?
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
<br />
        <select name="type" id="type">
          <option value="">All Device Types</option>
          <?php

foreach (dbFetch('SELECT `type` FROM `devices` AS D WHERE 1 GROUP BY `type` ORDER BY `type`') as $data)
{
  if ($data['type'])
  {
    echo("<option value='".$data['type']."'");
    if ($data['type'] == $vars['type']) { echo(" selected"); }
    echo(">".ucfirst($data['type'])."</option>");
  }
}
          ?>
        </select>

      </td>
      <td align="center">
      <a href="<?php echo(generate_url($vars)); ?>" title="Update the browser URL to reflect the search criteria." >Update URL</a> |
      <a href="<?php echo(generate_url(array('page' => 'devices', 'section' => $vars['section'], 'bare' => $vars['bare']))); ?>" title="Reset critera to default." >Reset</a>
      <br />
      <input type="submit" class="submit" value="Search">
      </td>
    </tr>
  </table>
</form>

<hr />

<?php

}

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

$menu_options = array('bits'      => 'Bits',
                      'processor' => 'CPU',
                      'mempool'   => 'Memory',
                      'uptime'    => 'Uptime',
                      'storage'   => 'Storage',
                      'diskio'    => 'Disk I/O'
                      );
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

?>

<div style="float: right;">

<?php

  if ($vars['searchbar'] == "hide")
  {
    echo('<a href="'. generate_url($vars, array('searchbar' => '')).'">Restore Search</a>');
  } else {
    echo('<a href="'. generate_url($vars, array('searchbar' => 'hide')).'">Remove Search</a>');
  }

  echo("  | ");

  if ($vars['bare'] == "yes")
  {
    echo('<a href="'. generate_url($vars, array('bare' => '')).'">Restore Header</a>');
  } else {
    echo('<a href="'. generate_url($vars, array('bare' => 'yes')).'">Remove Header</a>');
  }

?>

</div>

<?php

print_optionbar_end();

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

  echo('<table cellspacing="0" class="devicetable sortable" width="100%">');
  if ($subformat == "detail")
  {
    echo('<tr class="tablehead">
    <th></th>
    <th></th>
    <th class="paddedcell">Device</th>
    <th></th>
    <th class="paddedcell">Platform</th>
    <th class="paddedcell">Operating System</th>
    <th class="paddedcell">Uptime/Location</th>
  </tr>');
  }

  foreach (dbFetchRows($query, $sql_param) as $device)
  {
    if (device_permitted($device['device_id']))
    {
      if (!$location_filter || ((get_dev_attrib($device,'override_sysLocation_bool') && get_dev_attrib($device,'override_sysLocation_string') == $location_filter)
        || $device['location'] == $location_filter))
      {
        if ($subformat == "detail")
        {
          include("includes/hostbox.inc.php");
        } else {
          include("includes/hostbox-basic.inc.php");
        }
      }
    }
  }
  echo("</table>");
}

?>
