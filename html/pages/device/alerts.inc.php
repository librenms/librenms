<?php

$glo_conditions = cache_conditions_global();

#echo("<pre>");
#print_r($glo_conditions);
#echo("</pre>");

echo('<table cellspacing="0" cellpadding=5 class="sortable" width="100%">
  <tr class="tablehead">
    <th width=10></th>
    <th class=paddedcell width=150>Type</th>
    <th class=paddedcell width=150>Subtype</th>
    <th class=paddedcell width=150>Metric</th>
    <th class=paddedcell width=100>Operator</th>
    <th class=paddedcell width=100>Value</th>
    <th class=paddedcell width=75>Severity</th>
    <th class=paddedcell width=50>On</th>
    <th class=paddedcell width=50>Status</th>
    <th class=paddedcell></th>
  </tr>');

function get_entity_list($type, $subtype = "*", $device_id = "*", $entry)
{
  if($type == "storage") { $table = $type; } else { $table = $type.'s'; }

  $query = 'SELECT '.$type.'_id AS id FROM '.$table.' WHERE 1';
  $args  = array();

  if(is_numeric($device_id))
  {
    $query .= " AND device_id = ?";
    $args[] = $device_id;
  }

  if(is_numeric($entry['entity']))
  {
    $query .= " AND ".$type."_id = ?";
    $args[] = $entry['entity'];
  }

  $entities_db = dbFetchRows($query, $args);

  foreach($entities_db as $entity_db)
  {
    $entities[] = $entity_db['id'];
  }
  return $entities;
}

foreach($glo_conditions as $type => $subtypes)
{
  foreach($subtypes as $subtype => $metrics)
  {
    if(empty($subtype)) { $subtype = "*"; }
    foreach($metrics as $metric => $entries)
    {
      foreach($entries as $entry_id => $entry)
      {
        if($entry['enable'] == 1) { $enabled = '<img align=absmiddle src="images/16/tick.png" />'; } else { $enabled = '<img align=absmiddle src="images/16/stop.png" />'; }
        echo('<tr class=list>');
        echo('<td></td><td><strong>'.$type.'</strong></td><td>'.$subtype.'</td><td>'.$metric.'</td><td>'.htmlentities($entry['operator']).'</td><td>'.$entry['value'].'</td><td>'.$entry['severity'].'</td><td>'.$enabled.'</td>');
        echo('<td></td><td></td>');
        echo('</tr>');

        /// Get which entities match this checker
        $entities = get_entity_list($type, $subtype, $device['device_id'], $entry['entity']);

        if(!empty($entities))
        {
          echo('<tr class=list><td></td><td colspan=9><strong>Entities: </strong>');
	  foreach($entities as $entity)
          {
           echo(generate_entity_link($type, $entity)." ");
          }
          echo('</td></tr>');
        }
      }
    }
  }
}


echo('</table>');

?>
