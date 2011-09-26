<?php

if (!is_integer($i/2)) { $bg_colour = $list_colour_a; } else { $bg_colour = $list_colour_b; }

echo('<tr bgcolor="' . $bg_colour . '">');

echo('<td class="list">');

if (array_search($vm['vmwVmDisplayName'],array_keys($cache['devices'])))
{
  echo(generate_device_link($cache['devices'][$vm['vmwVmDisplayName']]));
} else {
  echo $vm['vmwVmDisplayName'];
}

echo("</td>");
echo('<td class="list">' . $vm['vmwVmState'] . "</td>");

if ($vm['vmwVmGuestOS'] == "E: tools not installed")
{
  echo('<td class="box-desc">Unknown (VMware Tools not installed)</td>');
}
else if ($vm['vmwVmGuestOS'] == "")
{
  echo('<td class="box-desc"><i>(Unknown)</i></td>');
} else {
  echo('<td class="list">' . $config['vmware_guestid'][$vm['vmwVmGuestOS']] . "</td>");
}

if ($vm['vmwVmMemSize'] >= 1024)
{
  echo("<td class=list>" . sprintf("%.2f",$vm['vmwVmMemSize']/1024) . " GB</td>");
} else {
  echo("<td class=list>" . sprintf("%.2f",$vm['vmwVmMemSize']) . " MB</td>");
}

echo('<td class="list">' . $vm['vmwVmCpus'] . " CPU</td>");

?>
