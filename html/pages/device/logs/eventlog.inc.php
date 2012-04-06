<hr />

  <form method="post" action="">
  <label><strong>Search</strong>
    <input type="text" name="string" id="string" value="<?php echo($_POST['string']); ?>" />
  </label>
  <label>
    <strong>Type</strong>
    <select name="type" id="type">
      <option value="">All Types</option>
      <option value="system">System</option>
      <?php
        foreach (dbFetchRows("SELECT `type` FROM `eventlog` WHERE device_id = ? GROUP BY `type` ORDER BY `type`", array($device['device_id'])) as $data)
        {
          echo("<option value='".$data['type']."'");
          if ($data['type'] == $_POST['type']) { echo("selected"); }
          echo(">".$data['type']."</option>");
        }
      ?>
    </select>
  </label>
  <input class=submit type=submit value=Search>
</form>

<?php

print_optionbar_end();

$entries = dbFetchRows("SELECT *,DATE_FORMAT(datetime, '%D %b %Y %T') as humandate  FROM `eventlog` WHERE `host` = ? ORDER BY `datetime` DESC LIMIT 0,250", array($device['device_id']));
echo('<table cellspacing="0" cellpadding="2" width="100%">');

foreach ($entries as $entry)
{
  include("includes/print-event.inc.php");
}

echo('</table>');

$pagetitle[] = "Events";

?>
