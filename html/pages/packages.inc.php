<?php

foreach ($vars as $var => $value)
{
  if ($value != "")
  {
    switch ($var)
    {
      case 'name':
        $where .= " AND `$var` LIKE ?";
        $param[] = "%".$value."%";
        break;
    }
  }
}

echo('<table cellspacing="0" cellpadding="5" width="100%">');

$i=0;
foreach (dbFetchRows("SELECT * FROM `packages` WHERE 1 $where GROUP BY `name`", $param) as $entry)
{
  if (!is_integer($i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }
  echo("<tr bgcolor=$row_colour>");
  echo('<td width=200><a href="'. generate_url($vars, array('name' => $entry['name'])).'">'.$entry['name'].'</a></td>');

  echo("<td>");
  foreach (dbFetchRows("SELECT * FROM `packages` WHERE `name` = ? ORDER BY version, build", array($entry['name'])) as $entry_v)
  {
    $entry['blah'][$entry_v['version']][$entry_v['build']][$entry_v['device_id']] = 1;
  }

  foreach ($entry['blah'] as $version => $bleu) 
  {

    $content = '<div style="width: 800px;">';

    foreach ($bleu as $build => $bloo)
    {
      $content .= '<div style="background-color: #eeeeee; margin: 5px;"><span style="font-weight: bold; ">'.$version.'-'.$build.'</span>';
      foreach ($bloo as $device_id => $no)
      {
        $this_device = device_by_id_cache($device_id);
        $content .= '<span style="background-color: #f5f5f5; margin: 5px;">'.$this_device['hostname'].'</span> ';

      }
      $content .= "</div>";
    }
    $content .= "</div>";

    echo("<span style='margin:5px;'>".overlib_link("", $version, $content,  NULL)."</span>");
  }

  echo("<td>");

  echo("</tr>");

  $i++;

}

echo("</table>");

?>
