<?php

if ($_SESSION['userlevel'] < 10)
{
  include("includes/error-no-perm.inc.php");
  exit;
}

if ($_REQUEST['id'])
{
  echo(delete_device(mres($_REQUEST['id'])));
}

?>

<form name="form1" method="post" action="">
  <h1>Delete Host</h1>
  <br />
  <p><select name="id">

<?php

$query = mysql_query("SELECT * FROM `devices` ORDER BY `hostname`");

while ($data = mysql_fetch_assoc($query))
{
  echo("<option value='".$data['device_id']."'>".$data['hostname']."</option>");
}

?>
    </select>

    <input type="submit" class="submit" name="Submit" value="Delete Host">
</p>
</form>
