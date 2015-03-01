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

if (isset($vars['hostname'])) { $where .= " AND hostname LIKE ?"; $sql_param[] = "%".$vars['hostname']."%"; }
if (isset($vars['os']))       { $where .= " AND os = ?";          $sql_param[] = $vars['os']; }
if (isset($vars['version']))  { $where .= " AND version = ?";     $sql_param[] = $vars['version']; }
if (isset($vars['hardware'])) { $where .= " AND hardware = ?";    $sql_param[] = $vars['hardware']; }
if (isset($vars['features'])) { $where .= " AND features = ?";    $sql_param[] = $vars['features']; }
if (isset($vars['type']))     { $where .= " AND type = ?";        $sql_param[] = $vars['type']; }
if (isset($vars['state']))    {
  $where .= " AND status= ?";       $sql_param[] = $state;
  $where .= " AND disabled='0' AND `ignore`='0'"; $sql_param[] = '';
}
if (isset($vars['disabled'])) { $where .= " AND disabled= ?";     $sql_param[] = $vars['disabled']; }
if (isset($vars['ignore']))   { $where .= " AND `ignore`= ?";       $sql_param[] = $vars['ignore']; }
if (isset($vars['location']) && $vars['location'] == "Unset") { $location_filter = ''; }
if (isset($vars['location'])) { $location_filter = $vars['location']; }

$pagetitle[] = "Devices";

print_optionbar_start();

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

  if (isset($vars['searchbar']) && $vars['searchbar'] == "hide")
  {
    echo('<a href="'. generate_url($vars, array('searchbar' => '')).'">Restore Search</a>');
  } else {
    echo('<a href="'. generate_url($vars, array('searchbar' => 'hide')).'">Remove Search</a>');
  }

  echo("  | ");

  if (isset($vars['bare']) && $vars['bare'] == "yes")
  {
    echo('<a href="'. generate_url($vars, array('bare' => '')).'">Restore Header</a>');
  } else {
    echo('<a href="'. generate_url($vars, array('bare' => 'yes')).'">Remove Header</a>');
  }
?>

</div>

<?php

print_optionbar_end();
print_optionbar_start();

if((isset($vars['searchbar']) && $vars['searchbar'] != "hide") || !isset($vars['searchbar']))
{

?>
<form method="post" action="" class="form-inline" role="form">
  <div class="form-group">
    <input type="text" name="hostname" id="hostname" size="38" value="<?php echo($vars['hostname']); ?>" class="form-control input-sm" placeholder="Hostname"/>
  </div>
  <div class="form-group">
    <select name='os' id='os' class="form-control input-sm">
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
    <select name='version' id='version' class="form-control input-sm">
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
  </div>
  <div class="form-group">
    <select name="hardware" id="hardware" class="form-control input-sm">
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
    <select name="features" id="features" class="form-control input-sm">
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
  </div>
  <div class="form-group">
    <select name="location" id="location" class="form-control input-sm">
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
    <select name="type" id="type" class="form-control input-sm">
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
    <button type="submit" class="btn btn-default input-sm">Search</button>
  </div>
  <div class="form-group">
    <a href="<?php echo(generate_url($vars)); ?>" title="Update the browser URL to reflect the search criteria." >Update URL</a> |
    <a href="<?php echo(generate_url(array('page' => 'devices', 'section' => $vars['section'], 'bare' => $vars['bare']))); ?>" title="Reset critera to default." >Reset</a>
  </div>
</form>

<?php

}

print_optionbar_end();

$query = "SELECT * FROM `devices` WHERE 1 ";

if (isset($where)) {
    $query .= $where;
}

$query .= " ORDER BY hostname";

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
    <th>Operating System</th>
    <th>Uptime/Location</th>
    <th>Actions</th>
  </tr>');
  }

  foreach (dbFetchRows($query, $sql_param) as $device)
  {
    if (device_permitted($device['device_id']))
    {
      if (!isset($location_filter) || ((get_dev_attrib($device,'override_sysLocation_bool') && get_dev_attrib($device,'override_sysLocation_string') == $location_filter)
        || $device['location'] == $location_filter))
      {
        $cell_click = 'onclick="location.href=\'device/device='.$device['device_id'].'/\'" style="cursor: pointer;"';
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
  echo('</div>');
  echo('</div>');
}

?>
