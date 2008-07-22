<?php

if($_SESSION['userlevel'] < 10) {
  echo("<span class='alert'>You are not permitted to perform this function</span>");
  exit;
}

if($_POST['id']) {
  delHost(mres($_POST['id']));
} elseif (mres($_GET['id'])) {
  delHost(mres($_GET['id']));
}

?>

<form name="form1" method="post" action="?page=delhost">
  <h1>Delete Host</h1>
  <br />
  <p><select name="id">
<?php

$query = mysql_query("SELECT * FROM `devices` ORDER BY `hostname`");

while($data = mysql_fetch_array($query)) {

  echo("<option value='".$data['device_id']."'>".$data['hostname']."</option>");

}

?>
    </select>
  
    <input type="submit" name="Submit" value="Delete Host">
</p>
</form>

